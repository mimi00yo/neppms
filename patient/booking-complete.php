<?php
/**
 * booking-complete.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Responsible for safely finalizing a patient appointment booking.
 *
 * RACE CONDITION PREVENTION — 3 complementary layers:
 *
 *  Layer 1 │ PHP Transaction + SELECT FOR UPDATE
 *          │ Acquires an exclusive row-lock on `schedule` before reading
 *          │ booked_count. A concurrent request BLOCKS here until we COMMIT
 *          │ or ROLLBACK — guaranteeing serially-consistent counts.
 *
 *  Layer 2 │ DB-level UNIQUE constraints (hard stop)
 *          │   UNIQUE KEY uq_patient_session (pid, scheduleid)
 *          │   UNIQUE KEY uq_slot_booking    (slot_id)
 *          │ Even if two transactions somehow both pass PHP checks,
 *          │ MySQL will reject the second INSERT with errno 1062.
 *
 *  Layer 3 │ Application validation (fast-fail before hitting DB)
 *          │ Checks: session exists, not expired, not full, no duplicate,
 *          │ slot is free — all validated inside the locked transaction.
 * ─────────────────────────────────────────────────────────────────────────────
 */

session_start();

// ── 0. Auth guard ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user']) || $_SESSION['user'] === '' || $_SESSION['usertype'] !== 'p') {
    header('Location: ../login.php');
    exit;
}
$useremail = $_SESSION['user'];

include('../connection.php');

// Ensure the table engine is InnoDB so FOR UPDATE works
// (MyISAM doesn't support row-level locking; existing tables may need checking)

// ── 1. Only handle POST with the submit button present ────────────────────
if (!$_POST || !isset($_POST['booknow'])) {
    header('Location: booking.php?step=1');
    exit;
}

// ── 2. Input validation ───────────────────────────────────────────────────
$scheduleid = (int)($_POST['scheduleid'] ?? 0);
$slot_id    = (int)($_POST['slot_id']    ?? 0);   // 0 = no named slot (calculated)
$slot_num   = (int)($_POST['slot_num']   ?? 0);   // chosen token number for calculated path
$date       = trim($_POST['date']        ?? '');

$errors = [];

if ($scheduleid <= 0)                      $errors[] = 'Invalid session.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors[] = 'Invalid booking date.';

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: booking.php?step=1&err=" . $msg);
    exit;
}

// ── 3. Fetch patient record ───────────────────────────────────────────────
$pstmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
$pstmt->bind_param('s', $useremail);
$pstmt->execute();
$patient = $pstmt->get_result()->fetch_assoc();
$pstmt->close();

if (!$patient) {
    header('Location: ../login.php');
    exit;
}
$patient_id = (int)$patient['pid'];


// ═══════════════════════════════════════════════════════════════════════════
// CORE BOOKING LOGIC — wrapped in a transaction with row-level locks
// ═══════════════════════════════════════════════════════════════════════════

$database->autocommit(false);   // Begin manual transaction
$booking_error = null;
$assigned_token = null;

try {

    // ──────────────────────────────────────────────────────────────────────
    // LOCK A: Lock the schedule row so no concurrent request can read a
    //         stale booked_count while we are in the middle of inserting.
    //         SELECT … FOR UPDATE holds an exclusive lock until COMMIT.
    // SQL:
    //   SELECT s.scheduleid, s.nop, COUNT(a.appoid) AS booked_count
    //   FROM schedule s
    //   LEFT JOIN appointment a ON a.scheduleid = s.scheduleid
    //   WHERE s.scheduleid = ?
    //   GROUP BY s.scheduleid
    //   FOR UPDATE
    // ──────────────────────────────────────────────────────────────────────
    $lock_stmt = $database->prepare("
        SELECT
            s.scheduleid,
            s.nop                       AS token_limit,
            s.scheduledate,
            d.doc_slot_duration,
            s.scheduletime,
            COUNT(a.appoid)             AS booked_count,
            (s.nop - COUNT(a.appoid))   AS tokens_remaining
        FROM schedule s
        JOIN doctor d ON d.docid = s.docid
        LEFT JOIN appointment a ON a.scheduleid = s.scheduleid
        WHERE s.scheduleid = ?
        GROUP BY s.scheduleid
        FOR UPDATE
    ");
    if (!$lock_stmt) throw new Exception('Prepare failed: ' . $database->error);

    $lock_stmt->bind_param('i', $scheduleid);
    $lock_stmt->execute();
    $session = $lock_stmt->get_result()->fetch_assoc();
    $lock_stmt->close();

    // ── Validation A: Session must exist ─────────────────────────────────
    if (!$session) {
        throw new Exception('session_not_found');
    }

    $token_limit      = (int)$session['token_limit'];
    $booked_count     = (int)$session['booked_count'];
    $tokens_remaining = (int)$session['tokens_remaining'];

    // ── Validation B: Session date must not be in the past ───────────────
    if ($session['scheduledate'] < date('Y-m-d')) {
        throw new Exception('session_expired');
    }

    // ── Validation C: Token limit not exceeded ───────────────────────────
    //    Because we hold a FOR UPDATE lock on this row, no other transaction
    //    can increment booked_count between our check and our INSERT.
    // SQL (conceptual):
    //   IF COUNT(appointment.scheduleid) >= schedule.nop → REJECT
    if ($booked_count >= $token_limit) {
        throw new Exception('session_full');
    }

    // ── Validation D: Duplicate booking — same patient, same session ──────
    $reschedule_id = isset($_SESSION['reschedule_id']) ? (int)$_SESSION['reschedule_id'] : 0;
    $dup_q = "SELECT COUNT(*) AS c FROM appointment WHERE scheduleid = ? AND pid = ?";
    if($reschedule_id > 0) $dup_q .= " AND appoid != ?";
    $dup_stmt = $database->prepare($dup_q);
    if($reschedule_id > 0) $dup_stmt->bind_param('iii', $scheduleid, $patient_id, $reschedule_id);
    else $dup_stmt->bind_param('ii', $scheduleid, $patient_id);
    $dup_stmt->execute();
    $dup_count = (int)$dup_stmt->get_result()->fetch_assoc()['c'];
    $dup_stmt->close();
    if ($dup_count > 0) throw new Exception('already_booked');

    // ── Validation E: Time Conflict Check ────────────────────────────────
    // Check if patient has any OTHER appointment at the exact same time/date
    // We need to compare the 'slot_time' (or calculated time)
    $new_time = "";
    if ($slot_id > 0) {
        $ts_q = $database->prepare("SELECT slot_time FROM timeslots WHERE slot_id = ?");
        $ts_q->bind_param("i", $slot_id);
        $ts_q->execute();
        $new_time = $ts_q->get_result()->fetch_assoc()['slot_time'];
        $ts_q->close();
    } else {
        // Calculate based on token
        $offset_mins = ($assigned_token - 1) * (int)$session['doc_slot_duration'];
        $new_time = date('H:i:s', strtotime("+$offset_mins minutes", strtotime($session['scheduletime'])));
    }

    $conf_q = $database->prepare("
        SELECT a.appoid, s.scheduletime, t.slot_time, d.doc_slot_duration, a.apponum
        FROM appointment a
        INNER JOIN schedule s ON a.scheduleid = s.scheduleid
        INNER JOIN doctor d ON s.docid = d.docid
        LEFT JOIN timeslots t ON a.slot_id = t.slot_id
        WHERE a.pid = ? AND s.scheduledate = ? AND a.appoid != ?
    ");
    $conf_q->bind_param("isi", $patient_id, $session['scheduledate'], $reschedule_id);
    $conf_q->execute();
    $conf_res = $conf_q->get_result();
    while($conf_row = $conf_res->fetch_assoc()){
        $existing_time = $conf_row['slot_time'];
        if(empty($existing_time)){
            $off = ($conf_row['apponum'] - 1) * (int)$conf_row['doc_slot_duration'];
            $existing_time = date('H:i:s', strtotime("+$off minutes", strtotime($conf_row['scheduletime'])));
        }
        
        if($existing_time == $new_time){
            throw new Exception('time_conflict');
        }
    }
    $conf_q->close();

    // ── Validation E: Named time slot check & GREEDY REARRANGEMENT ────────
    if ($slot_id > 0) {
        // Lock the specific timeslot row FOR UPDATE
        $slot_stmt = $database->prepare("
            SELECT slot_id, slot_number, slot_time, is_booked
            FROM timeslots
            WHERE slot_id = ?
            FOR UPDATE
        ");
        $slot_stmt->bind_param('i', $slot_id);
        $slot_stmt->execute();
        $slot_row = $slot_stmt->get_result()->fetch_assoc();
        $slot_stmt->close();

        if (!$slot_row) {
            throw new Exception('slot_not_found');
        }
        
        // GREEDY APPROACH INTERCEPTION 
        // If the targeted slot is already booked, we do NOT throw an error.
        // We automatically and greedily fetch the next earliest free slot.
        if ((int)$slot_row['is_booked'] === 1) {
            $greedy_stmt = $database->prepare("
                SELECT slot_id, slot_number, slot_time
                FROM timeslots
                WHERE scheduleid = ? AND is_booked = 0
                ORDER BY slot_time ASC
                LIMIT 1
                FOR UPDATE
            ");
            $greedy_stmt->bind_param('i', $scheduleid);
            $greedy_stmt->execute();
            $slot_row = $greedy_stmt->get_result()->fetch_assoc();
            $greedy_stmt->close();

            // If the greedy traversal finds nothing, the session is fully booked.
            if (!$slot_row) {
                throw new Exception('session_full');
            }
            
            // Safely overwrite the target identifiers with the greedily acquired optimal slot
            $slot_id = (int)$slot_row['slot_id'];
        }
    }

    // ── Assign token number automatically (inside the lock) ────────────────────
    if ($slot_id > 0) {
        // Path A — Pre-generated / Greedy Slot Traversal explicitly dictates the token number
        $assigned_token = (int)$slot_row['slot_number'];
    } elseif ($slot_num > 0) {
        // Path B — Calculated fallback: patient chose a dynamically output chip
        $assigned_token = $slot_num;
    } else {
        // Absolute fallback — Find the smallest available token number (the "First Gap")
        $bt_stmt = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ? ORDER BY apponum ASC");
        $bt_stmt->bind_param('i', $scheduleid);
        $bt_stmt->execute();
        $bt_res = $bt_stmt->get_result();
        $booked_tokens = [];
        while ($bt_row = $bt_res->fetch_assoc()) {
            $booked_tokens[] = (int)$bt_row['apponum'];
        }
        $bt_stmt->close();

        $assigned_token = 1;
        while (in_array($assigned_token, $booked_tokens)) {
            $assigned_token++;
        }
    }

    if (isset($_SESSION['reschedule_id'])) {
        $reschedule_id = (int)$_SESSION['reschedule_id'];
        
        // 1. Fetch old slot_id to release it
        $old_stmt = $database->prepare("SELECT slot_id FROM appointment WHERE appoid = ?");
        $old_stmt->bind_param("i", $reschedule_id);
        $old_stmt->execute();
        $old_appo = $old_stmt->get_result()->fetch_assoc();
        $old_stmt->close();
        
        if ($old_appo && $old_appo['slot_id']) {
            $release_stmt = $database->prepare("UPDATE timeslots SET is_booked = 0 WHERE slot_id = ?");
            $release_stmt->bind_param("i", $old_appo['slot_id']);
            $release_stmt->execute();
            $release_stmt->close();
        }

        // 2. Update existing appointment
        $ins_stmt = $database->prepare("
            UPDATE appointment 
            SET apponum = ?, scheduleid = ?, appodate = ?, slot_id = ? 
            WHERE appoid = ?
        ");
        $ins_stmt->bind_param('iisii',
            $assigned_token,
            $scheduleid,
            $date,
            $slot_id_val,
            $reschedule_id
        );
        $mode = 'rescheduled';
    } else {
        // 3. Normal INSERT
        $ins_stmt = $database->prepare("
            INSERT INTO appointment (pid, apponum, scheduleid, appodate, slot_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins_stmt->bind_param('iiisi',
            $patient_id,
            $assigned_token,
            $scheduleid,
            $date,
            $slot_id_val
        );
        $mode = 'added';
    }

    if (!$ins_stmt->execute()) {
        throw new Exception('operation_failed: ' . $ins_stmt->error);
    }
    $ins_stmt->close();
    
    // Clear reschedule session
    unset($_SESSION['reschedule_id']);

    // ──────────────────────────────────────────────────────────────────────
    // UPDATE: Mark timeslot as booked (atomic — still inside transaction)
    // SQL:
    //   UPDATE timeslots SET is_booked = 1 WHERE slot_id = ?
    // ──────────────────────────────────────────────────────────────────────
    if ($slot_id > 0) {
        $mark_stmt = $database->prepare("
            UPDATE timeslots SET is_booked = 1 WHERE slot_id = ?
        ");
        $mark_stmt->bind_param('i', $slot_id);
        if (!$mark_stmt->execute()) {
            throw new Exception('slot_mark_failed: ' . $mark_stmt->error);
        }
        $mark_stmt->close();
    }

    // ── All good — commit everything atomically ───────────────────────────
    $database->commit();

} catch (mysqli_sql_exception $e) {
    // ── Layer 2 fires here: DB rejected with a unique key violation ──
    $database->rollback();
    // errno 1062 = Duplicate entry
    if ($e->getCode() === 1062) {
        // Identify which constraint was violated
        $error_msg = $e->getMessage();
        if (str_contains($error_msg, 'uq_patient_session')) {
            $booking_error = 'already_booked';
        } elseif (str_contains($error_msg, 'uq_slot_booking') || str_contains($error_msg, 'uq_schedule_apponum')) {
            // Either the named slot or the token number was just taken
            $booking_error = 'slot_taken';
        } else {
            $booking_error = 'insert_failed';
        }
    } else {
        $booking_error = 'insert_failed';
    }
} catch (Exception $e) {
    // Something went wrong — undo every change
    $database->rollback();
    $booking_error = $e->getMessage();
}

$database->autocommit(true); // Restore normal mode

// ── 4. Post-transaction: redirect with result ─────────────────────────────
if ($booking_error !== null) {

    // Map internal error codes → user-friendly redirect params
    $error_map = [
        'session_not_found' => ['step' => 1, 'err' => 'Session not found. Please start over.'],
        'session_expired'   => ['step' => 1, 'err' => 'This session date has already passed.'],
        'session_full'      => ['step' => 2, 'err' => 'Sorry, this session is now fully booked.'],
        'already_booked'    => ['step' => 2, 'err' => 'You already have a booking for this session.'],
        'slot_not_found'    => ['step' => 2, 'err' => 'The selected slot no longer exists.'],
        'slot_taken'        => ['step' => 2, 'err' => 'That slot was just taken. Please choose another.'],
        'time_conflict'     => ['step' => 2, 'err' => 'Alert: You already have another appointment at this exact time.'],
        'insert_failed'     => ['step' => 1, 'err' => 'Booking failed due to a database error. Please try again.'],
        'slot_mark_failed'  => ['step' => 1, 'err' => 'Booking failed during slot assignment. Please try again.'],
    ];

    // Find matching or fallback
    $redirect = $error_map[$booking_error] ?? ['step' => 1, 'err' => 'An unexpected error occurred.'];

    header("Location: booking.php?step={$redirect['step']}&err=" . urlencode($redirect['err']));
    exit;
}

// ── 5. Success ────────────────────────────────────────────────────────────
header("Location: appointment.php?action=booking-".$mode."&id={$assigned_token}&titleget=none");
exit;
?>
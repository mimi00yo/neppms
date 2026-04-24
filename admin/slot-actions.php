<?php
/**
 * slot-actions.php
 * Admin POST handler for:
 *   - Updating a doctor's token limit & slot duration
 *   - Creating, updating, and deleting time slots
 */

session_start();

// --- Auth Guard ---
if (!isset($_SESSION['user']) || $_SESSION['user'] === '' || $_SESSION['usertype'] !== 'a') {
    header('Location: ../login.php');
    exit;
}

include('../connection.php');

// Helper: sanitize string input
function clean($db, $value) {
    return $database->real_escape_string(trim($value));
}

// Helper: redirect back with a status message
function redirect($page, $status, $message) {
    header("Location: $page?status=$status&msg=" . urlencode($message));
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // =========================================================
    // ACTION: Update doctor's token limit and slot duration
    // =========================================================
    case 'update_token_limit':
        $docid        = (int)($_POST['docid'] ?? 0);
        $max_tokens   = (int)($_POST['max_tokens'] ?? 0);
        $slot_duration = (int)($_POST['slot_duration'] ?? 0);

        // Validation
        if ($docid <= 0) {
            redirect('token-slots.php', 'error', 'Invalid doctor selected.');
        }
        if ($max_tokens < 1 || $max_tokens > 200) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Token limit must be between 1 and 200.');
        }
        if ($slot_duration < 5 || $slot_duration > 120) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Slot duration must be between 5 and 120 minutes.');
        }

        $stmt = $database->prepare(
            "UPDATE doctor SET doc_max_tokens = ?, doc_slot_duration = ? WHERE docid = ?"
        );
        $stmt->bind_param('iii', $max_tokens, $slot_duration, $docid);
        $stmt->execute();

        if ($stmt->affected_rows >= 0) {
            redirect('token-slots.php?doc=' . $docid, 'success', 'Token limit updated successfully.');
        } else {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Failed to update token limit.');
        }
        break;


    // =========================================================
    // ACTION: Create a new time slot for a schedule
    // =========================================================
    case 'create_slot':
        $scheduleid  = (int)($_POST['scheduleid'] ?? 0);
        $docid       = (int)($_POST['docid'] ?? 0);
        $slot_number = (int)($_POST['slot_number'] ?? 0);
        $slot_time   = trim($_POST['slot_time'] ?? '');

        // Validation
        if ($scheduleid <= 0) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid session. Please select a valid session.');
        }
        if ($slot_number < 1) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Token number must be at least 1.');
        }
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $slot_time)) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid time format. Use HH:MM.');
        }

        // Check for duplicate token number in same session
        $check = $database->prepare(
            "SELECT slot_id FROM timeslots WHERE scheduleid = ? AND slot_number = ?"
        );
        $check->bind_param('ii', $scheduleid, $slot_number);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            redirect('token-slots.php?doc=' . $docid, 'error', "Token #$slot_number already exists for this session.");
        }

        $stmt = $database->prepare(
            "INSERT INTO timeslots (scheduleid, slot_number, slot_time, is_booked) VALUES (?, ?, ?, 0)"
        );
        $stmt->bind_param('iis', $scheduleid, $slot_number, $slot_time);
        $stmt->execute();

        if ($stmt->insert_id > 0) {
            redirect('token-slots.php?doc=' . $docid, 'success', "Slot #$slot_number created at $slot_time.");
        } else {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Failed to create time slot.');
        }
        break;


    // =========================================================
    // ACTION: Update an existing time slot
    // =========================================================
    case 'update_slot':
        $slot_id    = (int)($_POST['slot_id'] ?? 0);
        $docid      = (int)($_POST['docid'] ?? 0);
        $slot_time  = trim($_POST['slot_time'] ?? '');

        // Validation
        if ($slot_id <= 0) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid slot selected.');
        }
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $slot_time)) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid time format. Use HH:MM.');
        }

        // Cannot edit a slot that already has a booking
        $booked_check = $database->prepare(
            "SELECT is_booked FROM timeslots WHERE slot_id = ?"
        );
        $booked_check->bind_param('i', $slot_id);
        $booked_check->execute();
        $booked_check->bind_result($is_booked);
        $booked_check->fetch();
        $booked_check->close();

        if ($is_booked) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Cannot edit a slot that is already booked.');
        }

        $stmt = $database->prepare(
            "UPDATE timeslots SET slot_time = ? WHERE slot_id = ?"
        );
        $stmt->bind_param('si', $slot_time, $slot_id);
        $stmt->execute();

        redirect('token-slots.php?doc=' . $docid, 'success', 'Slot time updated successfully.');
        break;


    // =========================================================
    // ACTION: Delete a time slot
    // =========================================================
    case 'delete_slot':
        $slot_id = (int)($_POST['slot_id'] ?? 0);
        $docid   = (int)($_POST['docid'] ?? 0);

        if ($slot_id <= 0) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid slot selected.');
        }

        // Safety check — do not delete booked slots
        $booked_check = $database->prepare(
            "SELECT is_booked FROM timeslots WHERE slot_id = ?"
        );
        $booked_check->bind_param('i', $slot_id);
        $booked_check->execute();
        $booked_check->bind_result($is_booked);
        $booked_check->fetch();
        $booked_check->close();

        if ($is_booked) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Cannot delete a booked slot. Cancel the appointment first.');
        }

        $stmt = $database->prepare("DELETE FROM timeslots WHERE slot_id = ?");
        $stmt->bind_param('i', $slot_id);
        $stmt->execute();

        redirect('token-slots.php?doc=' . $docid, 'success', 'Slot deleted successfully.');
        break;


    // =========================================================
    // ACTION: Auto-generate all slots for a session
    // =========================================================
    case 'auto_generate_slots':
        $scheduleid = (int)($_POST['scheduleid'] ?? 0);
        $docid      = (int)($_POST['docid'] ?? 0);

        if ($scheduleid <= 0 || $docid <= 0) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Invalid session or doctor.');
        }

        // Fetch session start time + doctor settings in one query
        $info = $database->prepare(
            "SELECT s.scheduletime, d.doc_max_tokens, d.doc_slot_duration
             FROM schedule s
             JOIN doctor d ON s.docid = d.docid
             WHERE s.scheduleid = ? AND d.docid = ?"
        );
        $info->bind_param('ii', $scheduleid, $docid);
        $info->execute();
        $info->bind_result($start_time, $max_tokens, $slot_duration);
        $info->fetch();
        $info->close();

        if (!$start_time) {
            redirect('token-slots.php?doc=' . $docid, 'error', 'Session not found.');
        }

        // Delete any existing unbooked slots first (safe cleanup)
        $database->prepare(
            "DELETE FROM timeslots WHERE scheduleid = ? AND is_booked = 0"
        )->execute();

        // Insert generated slots
        $insert = $database->prepare(
            "INSERT IGNORE INTO timeslots (scheduleid, slot_number, slot_time)
             VALUES (?, ?, ADDTIME(?, SEC_TO_TIME(? * 60)))"
        );

        $start_seconds_base = strtotime($start_time);
        $generated = 0;
        for ($i = 1; $i <= $max_tokens; $i++) {
            $offset_seconds = ($i - 1) * $slot_duration * 60;
            $slot_time = date('H:i:s', $start_seconds_base + $offset_seconds);
            $insert->bind_param('iiis', $scheduleid, $i, $slot_time, $offset_seconds); // keep simple
            // Simpler plain insert
            $st = "INSERT IGNORE INTO timeslots (scheduleid, slot_number, slot_time, is_booked) VALUES ($scheduleid, $i, '$slot_time', 0)";
            $database->query($st);
            $generated++;
        }

        redirect('token-slots.php?doc=' . $docid, 'success', "$generated slots auto-generated for this session.");
        break;


    default:
        redirect('token-slots.php', 'error', 'Unknown action.');
}
?>

<?php
/**
 * get-slots.php
 * AJAX endpoint: Returns available (unbooked) time slots for a given scheduleid.
 * Respects the doctor's token limit (nop on the schedule).
 *
 * GET params:
 *   scheduleid (int) - the session to fetch slots for
 *
 * Returns: JSON array of available slot objects
 */

session_start();

// Auth: only logged-in patients may call this
if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'p') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
include('../connection.php');

$scheduleid = (int)($_GET['scheduleid'] ?? 0);

if ($scheduleid <= 0) {
    echo json_encode(['error' => 'Invalid schedule ID']);
    exit;
}

// ─────────────────────────────────────────────────────────────
// QUERY 1: Check token limit vs. current bookings
// Ensures we respect doc_max_tokens / nop ceiling.
// ─────────────────────────────────────────────────────────────
$limit_sql = "
    SELECT
        s.scheduleid,
        s.title,
        s.scheduledate,
        s.scheduletime,
        s.nop                                       AS token_limit,
        d.doc_max_tokens,
        d.doc_slot_duration,
        COUNT(a.appoid)                             AS booked_count,
        (s.nop - COUNT(a.appoid))                   AS tokens_remaining
    FROM schedule s
    JOIN doctor d   ON s.docid = d.docid
    LEFT JOIN appointment a ON a.scheduleid = s.scheduleid
    WHERE s.scheduleid = ?
    GROUP BY s.scheduleid
";

$stmt = $database->prepare($limit_sql);
$stmt->bind_param('i', $scheduleid);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    echo json_encode(['error' => 'Session not found']);
    exit;
}

// Session is fully booked — return early with a clear message
if ($session['tokens_remaining'] <= 0) {
    echo json_encode([
        'session'          => $session,
        'fully_booked'     => true,
        'slots'            => [],
        'message'          => 'This session is fully booked.'
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────
// QUERY 2: Fetch slots from the timeslots table.
// We now fetch ALL slots (booked or not) to show them as disabled.
// ─────────────────────────────────────────────────────────────
$slots_stmt = $database->prepare("
    SELECT
        t.slot_id,
        t.slot_number,
        t.slot_time,
        t.is_booked
    FROM timeslots t
    WHERE t.scheduleid = ?
    ORDER BY t.slot_number ASC
");

$slots_stmt->bind_param('i', $scheduleid);
$slots_stmt->execute();
$result = $slots_stmt->get_result();
$slots  = $result->fetch_all(MYSQLI_ASSOC);

// ─────────────────────────────────────────────────────────────
// FALLBACK: No pre-generated timeslots exist — calculate them
// dynamically and check against existing appointments.
// ─────────────────────────────────────────────────────────────
if (empty($slots)) {
    $start_ts     = strtotime($session['scheduletime']);
    $duration     = (int)$session['doc_slot_duration'];
    $token_limit  = (int)$session['token_limit'];

    // Fetch already booked token numbers for this session
    $booked_tokens = [];
    $bt_stmt = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ?");
    $bt_stmt->bind_param('i', $scheduleid);
    $bt_stmt->execute();
    $bt_res = $bt_stmt->get_result();
    while ($bt_row = $bt_res->fetch_assoc()) {
        $booked_tokens[] = (int)$bt_row['apponum'];
    }

    for ($i = 1; $i <= $token_limit; $i++) {
        $offset_secs = ($i - 1) * $duration * 60;
        $is_taken = in_array($i, $booked_tokens) ? 1 : 0;
        $slots[] = [
            'slot_id'     => null,
            'slot_number' => $i,
            'slot_time'   => date('H:i:s', $start_ts + $offset_secs),
            'is_booked'   => $is_taken
        ];
    }
}

// Format times for display before sending to browser
foreach ($slots as &$slot) {
    $slot['slot_time_display'] = date('h:i A', strtotime($slot['slot_time']));
}
unset($slot);

echo json_encode([
    'session'      => [
        'title'     => $session['title'],
        'date'      => $session['scheduledate'],
        'time'      => date('h:i A', strtotime($session['scheduletime'])),
        'remaining' => $session['tokens_remaining'],
        'limit'     => $session['token_limit']
    ],
    'fully_booked' => false,
    'slots'        => $slots
]);
?>

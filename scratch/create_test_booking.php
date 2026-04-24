<?php
include("C:/xampp/htdocs/edoc/connection.php");

$yesterday = date('Y-m-d', strtotime('-1 day'));
$today = date('Y-m-d');

echo "Creating test data for yesterday ($yesterday)...\n";

// 1. Create a schedule for yesterday
$sql_sch = "INSERT INTO schedule (docid, title, scheduledate, scheduletime, nop, is_cancelled) 
            VALUES (1, 'Past Test Session', '$yesterday', '10:00:00', 10, 0)";
if ($database->query($sql_sch)) {
    $scheduleid = $database->insert_id;
    echo "Created schedule ID: $scheduleid\n";
    
    // 2. Create a timeslot for it
    $sql_slot = "INSERT INTO timeslots (scheduleid, slot_time, is_booked) 
                 VALUES ($scheduleid, '10:00:00', 1)";
    if ($database->query($sql_slot)) {
        $slot_id = $database->insert_id;
        echo "Created slot ID: $slot_id\n";
        
        // 3. Create an appointment for patient 1
        $sql_app = "INSERT INTO appointment (pid, apponum, scheduleid, appodate, slot_id) 
                    VALUES (1, 1, $scheduleid, '$yesterday', $slot_id)";
        if ($database->query($sql_app)) {
            echo "Created appointment for Patient 1 (Yesterday).\n";
        } else {
            echo "Error creating appointment: " . $database->error . "\n";
        }
    } else {
        echo "Error creating slot: " . $database->error . "\n";
    }
} else {
    echo "Error creating schedule: " . $database->error . "\n";
}
?>

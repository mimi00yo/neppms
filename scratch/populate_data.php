<?php
include("C:/xampp/htdocs/edoc/connection.php");

// 1. Get all specialties
$specialties = [];
$res = $database->query("SELECT * FROM specialties");
while($row = $res->fetch_assoc()) {
    $specialties[] = $row;
}

echo "Found " . count($specialties) . " specialties.\n";

// 2. Sample doctors for each specialty
$firstNames = ["James", "Mary", "Robert", "Patricia", "John", "Jennifer", "Michael", "Linda", "William", "Elizabeth"];
$lastNames = ["Smith", "Johnson", "Williams", "Brown", "Jones", "Garcia", "Miller", "Davis", "Rodriguez", "Martinez"];

foreach ($specialties as $index => $spec) {
    $spec_id = $spec['id'];
    $spec_name = $spec['sname'];
    
    // Check if doctor already exists for this specialty
    $check = $database->query("SELECT docid FROM doctor WHERE specialties = $spec_id");
    if ($check->num_rows == 0) {
        $name = "Dr. " . $firstNames[$index % 10] . " " . $lastNames[$index % 10];
        $email = strtolower($firstNames[$index % 10] . "." . $lastNames[$index % 10] . "@edoc.com");
        $tel = "011-2" . rand(100000, 999999);
        $nic = rand(10000000, 99999999) . "V";
        
        $sql = "INSERT INTO doctor (docemail, docname, docpassword, docnic, doctel, specialties, doc_max_tokens, doc_slot_duration) 
                VALUES ('$email', '$name', '123', '$nic', '$tel', $spec_id, 20, 15)";
        $database->query($sql);
        echo "Created doctor $name for $spec_name\n";
    }
}

// 3. Create schedules for 2026 (some past, some future)
$doctors = [];
$res = $database->query("SELECT docid, docname FROM doctor");
while($row = $res->fetch_assoc()) $doctors[] = $row;

$titles = ["Regular Checkup", "Morning Session", "Evening Consultation", "Weekend Special", "Follow-up Clinic"];
$today = date('Y-m-d');
$currentYear = date('Y');

foreach ($doctors as $doc) {
    $docid = $doc['docid'];
    
    // Create 3 schedules for each doctor
    for ($i = 0; $i < 3; $i++) {
        $days_offset = rand(-10, 20); // Some in past, some in future
        $date = date('Y-m-d', strtotime("$today + $days_offset days"));
        
        // Only 2026
        if (date('Y', strtotime($date)) != $currentYear) continue;
        
        $title = $titles[rand(0, 4)];
        $time = rand(9, 17) . ":00:00";
        $nop = rand(10, 20);
        
        $sql = "INSERT INTO schedule (docid, title, scheduledate, scheduletime, nop, is_cancelled) 
                VALUES ($docid, '$title', '$date', '$time', $nop, 0)";
        $database->query($sql);
        $sid = $database->insert_id;
        
        // Create timeslots for the schedule
        for ($s = 0; $s < 5; $s++) {
            $slot_time = date('H:i:s', strtotime("$time + " . ($s * 15) . " minutes"));
            $database->query("INSERT INTO timeslots (scheduleid, slot_time, is_booked) VALUES ($sid, '$slot_time', 0)");
        }
    }
}

// 4. Ensure we have patients
$patient_emails = ["patient1@edoc.com", "patient2@edoc.com", "patient3@edoc.com"];
foreach ($patient_emails as $i => $email) {
    $check = $database->query("SELECT pid FROM patient WHERE pemail = '$email'");
    if ($check->num_rows == 0) {
        $name = "Patient " . ($i + 1);
        $sql = "INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) 
                VALUES ('$email', '$name', '123', 'No 123, Main Street', '998877661V', '1995-01-01', '077123456$i')";
        $database->query($sql);
    }
}

// 5. Create some appointments for this year
$patients = [];
$res = $database->query("SELECT pid FROM patient");
while($row = $res->fetch_assoc()) $patients[] = $row['pid'];

$schedules = [];
$res = $database->query("SELECT scheduleid, scheduledate FROM schedule WHERE scheduledate LIKE '2026-%'");
while($row = $res->fetch_assoc()) $schedules[] = $row;

foreach ($patients as $pid) {
    // Each patient gets 2 appointments
    for ($k = 0; $k < 2; $k++) {
        $sch = $schedules[rand(0, count($schedules)-1)];
        $sid = $sch['scheduleid'];
        $date = $sch['scheduledate'];
        
        // Find an unbooked slot
        $slot_res = $database->query("SELECT slot_id FROM timeslots WHERE scheduleid = $sid AND is_booked = 0 LIMIT 1");
        if ($slot_res->num_rows > 0) {
            $slot = $slot_res->fetch_assoc();
            $slot_id = $slot['slot_id'];
            
            $database->query("INSERT INTO appointment (pid, apponum, scheduleid, appodate, slot_id) VALUES ($pid, " . ($k + 1) . ", $sid, '$date', $slot_id)");
            $database->query("UPDATE timeslots SET is_booked = 1 WHERE slot_id = $slot_id");
        }
    }
}

echo "Finished populating data.\n";
?>

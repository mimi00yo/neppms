<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Syncing Webuser table...\n";

// Clear existing doctor/patient webusers
$database->query("DELETE FROM webuser WHERE usertype IN ('d', 'p')");

// Sync Doctors
$res = $database->query("SELECT docemail FROM doctor");
while($row = $res->fetch_assoc()){
    $email = $row['docemail'];
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$email', 'd')");
}
echo "Synced Doctors: " . $database->affected_rows . " added.\n";

// Sync Patients
$res = $database->query("SELECT pemail FROM patient");
while($row = $res->fetch_assoc()){
    $email = $row['pemail'];
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$email', 'p')");
}
echo "Synced Patients: " . $database->affected_rows . " added.\n";

echo "Webuser table is now in sync.\n";
?>

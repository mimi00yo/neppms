<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Making all emails unique and syncing...\n";

// Function to clean and unique email
function make_unique_doctor_emails($database) {
    $res = $database->query("SELECT docid, docname FROM doctor");
    while($row = $res->fetch_assoc()){
        $id = $row['docid'];
        $name = strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z ]/', '', $row['docname'])));
        if (strpos($name, 'dr.') === 0) $name = substr($name, 3);
        $new_email = $name . $id . "@edoc.com";
        $database->query("UPDATE doctor SET docemail = '$new_email' WHERE docid = $id");
    }
}

function make_unique_patient_emails($database) {
    $res = $database->query("SELECT pid, pname FROM patient");
    while($row = $res->fetch_assoc()){
        $id = $row['pid'];
        $name = strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z ]/', '', $row['pname'])));
        $new_email = $name . $id . "@edoc.com";
        $database->query("UPDATE patient SET pemail = '$new_email' WHERE pid = $id");
    }
}

make_unique_doctor_emails($database);
echo "Doctors uniqueified.\n";

make_unique_patient_emails($database);
echo "Patients uniqueified.\n";

// Clear existing doctor/patient webusers
$database->query("DELETE FROM webuser WHERE usertype IN ('d', 'p')");

// Sync Doctors
$res = $database->query("SELECT docemail FROM doctor");
while($row = $res->fetch_assoc()){
    $email = $row['docemail'];
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$email', 'd')");
}
echo "Synced Doctors.\n";

// Sync Patients
$res = $database->query("SELECT pemail FROM patient");
while($row = $res->fetch_assoc()){
    $email = $row['pemail'];
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$email', 'p')");
}
echo "Synced Patients.\n";

echo "Webuser table is now in sync with unique emails.\n";
?>

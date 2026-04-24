<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Making all emails unique with role suffixes and syncing...\n";

// Clear existing doctor/patient webusers first to avoid PK violations during sync
$database->query("DELETE FROM webuser WHERE usertype IN ('d', 'p')");

// Function to uniqueify Doctors
$res = $database->query("SELECT docid, docname FROM doctor");
while($row = $res->fetch_assoc()){
    $id = $row['docid'];
    $name = strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z ]/', '', $row['docname'])));
    if (strpos($name, 'dr.') === 0) $name = substr($name, 3);
    $new_email = $name . $id . ".d@edoc.com";
    $database->query("UPDATE doctor SET docemail = '$new_email' WHERE docid = $id");
    // Add to webuser
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$new_email', 'd')");
}
echo "Doctors synced.\n";

// Function to uniqueify Patients
$res = $database->query("SELECT pid, pname FROM patient");
while($row = $res->fetch_assoc()){
    $id = $row['pid'];
    $name = strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z ]/', '', $row['pname'])));
    $new_email = $name . $id . ".p@edoc.com";
    $database->query("UPDATE patient SET pemail = '$new_email' WHERE pid = $id");
    // Add to webuser
    $database->query("INSERT INTO webuser (email, usertype) VALUES ('$new_email', 'p')");
}
echo "Patients synced.\n";

echo "All users are now unique and synced.\n";
?>

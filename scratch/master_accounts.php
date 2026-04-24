<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Adding master test accounts...\n";

// Update Doctor ID 1 to have doctor@edoc.com
$database->query("UPDATE doctor SET docemail = 'doctor@edoc.com' WHERE docid = 1");
$database->query("DELETE FROM webuser WHERE email = 'doctor@edoc.com'");
$database->query("INSERT INTO webuser (email, usertype) VALUES ('doctor@edoc.com', 'd')");

// Update Patient ID 1 to have patient@edoc.com
$database->query("UPDATE patient SET pemail = 'patient@edoc.com' WHERE pid = 1");
$database->query("DELETE FROM webuser WHERE email = 'patient@edoc.com'");
$database->query("INSERT INTO webuser (email, usertype) VALUES ('patient@edoc.com', 'p')");

echo "Master accounts 'doctor@edoc.com' and 'patient@edoc.com' are now active (Password: 123).\n";
?>

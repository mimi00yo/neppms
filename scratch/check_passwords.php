<?php
include("C:/xampp/htdocs/edoc/connection.php");
echo "Doctor Passwords:\n";
$r=$database->query("SELECT docemail, docpassword FROM doctor LIMIT 5");
while($f=$r->fetch_assoc()) echo "{$f['docemail']} -> '{$f['docpassword']}'\n";

echo "\nPatient Passwords:\n";
$r=$database->query("SELECT pemail, ppassword FROM patient LIMIT 5");
while($f=$r->fetch_assoc()) echo "{$f['pemail']} -> '{$f['ppassword']}'\n";
?>

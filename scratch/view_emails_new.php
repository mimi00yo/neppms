<?php
include("C:/xampp/htdocs/edoc/connection.php");
echo "Doctors (Use these to log in):\n";
$r=$database->query("SELECT docname, docemail FROM doctor LIMIT 5");
while($f=$r->fetch_assoc()) echo "{$f['docname']} -> {$f['docemail']}\n";
echo "\nPatients (Use these to log in):\n";
$r=$database->query("SELECT pname, pemail FROM patient LIMIT 5");
while($f=$r->fetch_assoc()) echo "{$f['pname']} -> {$f['pemail']}\n";
?>

<?php
include 'connection.php';
echo "--- Doctors ---\n";
$res = $database->query('SELECT docname, docemail FROM doctor');
while($row = $res->fetch_assoc()) echo $row['docname'] . " (" . $row['docemail'] . ")\n";
echo "\n--- Patients ---\n";
$res = $database->query('SELECT pname, pemail FROM patient');
while($row = $res->fetch_assoc()) echo $row['pname'] . " (" . $row['pemail'] . ")\n";
?>

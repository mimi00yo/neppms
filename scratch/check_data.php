<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "--- Patients ---\n";
$res = $database->query("SELECT pid, pname, pemail FROM patient LIMIT 5");
while($row = $res->fetch_assoc()) print_r($row);

echo "\n--- Doctors ---\n";
$res = $database->query("SELECT docid, docname FROM doctor LIMIT 5");
while($row = $res->fetch_assoc()) print_r($row);

echo "\n--- Schedules ---\n";
$res = $database->query("SELECT scheduleid, title, scheduledate FROM schedule LIMIT 5");
while($row = $res->fetch_assoc()) print_r($row);
?>

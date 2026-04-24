<?php
include 'connection.php';

echo "--- Doctors ---\n";
$res = $database->query("SELECT DISTINCT docname FROM doctor ORDER BY docname ASC");
while($row = $res->fetch_assoc()) echo $row['docname'] . "\n";

echo "\n--- Patients ---\n";
$res = $database->query("SELECT pname FROM patient ORDER BY pname ASC");
while($row = $res->fetch_assoc()) echo $row['pname'] . "\n";
?>

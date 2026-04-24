<?php
include("C:/xampp/htdocs/edoc/connection.php");
echo "--- schedule ---\n";
$result = $database->query("DESCRIBE schedule");
if ($result) { while ($row = $result->fetch_assoc()) { echo $row['Field'] . "\n"; } }
echo "\n--- appointment ---\n";
$result = $database->query("DESCRIBE appointment");
if ($result) { while ($row = $result->fetch_assoc()) { echo $row['Field'] . "\n"; } }
echo "\n--- patient ---\n";
$result = $database->query("DESCRIBE patient");
if ($result) { while ($row = $result->fetch_assoc()) { echo $row['Field'] . "\n"; } }
echo "\n--- doctor ---\n";
$result = $database->query("DESCRIBE doctor");
if ($result) { while ($row = $result->fetch_assoc()) { echo $row['Field'] . "\n"; } }
?>

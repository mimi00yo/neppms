<?php
include 'connection.php';
$res = $database->query("SELECT * FROM patient");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['pid'] . " | Name: [" . $row['pname'] . "] | Email: " . $row['pemail'] . "\n";
}
?>

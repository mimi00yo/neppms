<?php
include 'connection.php';
$res = $database->query("SELECT * FROM doctor");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['docid'] . " | Name: [" . $row['docname'] . "] | Email: " . $row['docemail'] . "\n";
}
?>

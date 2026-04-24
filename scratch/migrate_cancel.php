<?php
include("connection.php");
$res = $database->query("ALTER TABLE schedule ADD COLUMN is_cancelled TINYINT(1) DEFAULT 0");
if($res) echo "Success";
else echo "Error: " . $database->error;
?>

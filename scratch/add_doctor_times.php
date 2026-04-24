<?php
include("C:/xampp/htdocs/edoc/connection.php");
$database->query("ALTER TABLE doctor ADD COLUMN doc_start_time TIME DEFAULT '09:00:00'");
$database->query("ALTER TABLE doctor ADD COLUMN doc_end_time TIME DEFAULT '17:00:00'");
echo "Columns added successfully or already exist.\n";
?>

<?php
include("C:/xampp/htdocs/edoc/connection.php");
echo "Checking Doctor names...\n";
$r=$database->query("SELECT docid, docname FROM doctor");
while($f=$r->fetch_assoc()) echo "ID: {$f['docid']} -> Name: '{$f['docname']}'\n";
?>

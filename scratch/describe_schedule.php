<?php
include("C:/xampp/htdocs/edoc/connection.php");
$r=$database->query("DESCRIBE schedule");
while($f=$r->fetch_assoc()) echo "{$f['Field']} ({$f['Type']})\n";
?>

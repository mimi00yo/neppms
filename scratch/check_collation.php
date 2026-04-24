<?php
include("C:/xampp/htdocs/edoc/connection.php");
$r=$database->query("SHOW CREATE TABLE webuser");
$f=$r->fetch_assoc();
echo $f['Create Table'];
?>

<?php
include("C:/xampp/htdocs/edoc/connection.php");
$r=$database->query("SELECT email, usertype FROM webuser");
while($f=$r->fetch_assoc()) echo "{$f['email']} ({$f['usertype']})\n";
?>

<?php
include("C:/xampp/htdocs/edoc/connection.php");
echo "Admins:\n";
$r=$database->query("SELECT * FROM admin");
while($f=$r->fetch_assoc()) echo "{$f['aemail']} / {$f['apassword']}\n";

echo "\nFirst 10 Webusers:\n";
$r=$database->query("SELECT * FROM webuser LIMIT 10");
while($f=$r->fetch_assoc()) echo "{$f['email']} ({$f['usertype']})\n";
?>

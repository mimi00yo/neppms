<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Checking Webuser sync...\n\n";

echo "--- Webusers ---\n";
$res = $database->query("SELECT * FROM webuser");
while($row = $res->fetch_assoc()){
    echo "Email: {$row['email']}, Type: {$row['usertype']}\n";
}

echo "\n--- Doctors ---\n";
$res = $database->query("SELECT docemail, docname, docpassword FROM doctor");
while($row = $res->fetch_assoc()){
    $email = $row['docemail'];
    $check = $database->query("SELECT * FROM webuser WHERE email='$email' AND usertype='d'");
    if($check->num_rows == 0){
        echo "MISSING in webuser: {$email} ({$row['docname']})\n";
    } else {
        echo "OK: {$email}\n";
    }
}

echo "\n--- Patients ---\n";
$res = $database->query("SELECT pemail, pname, ppassword FROM patient");
while($row = $res->fetch_assoc()){
    $email = $row['pemail'];
    $check = $database->query("SELECT * FROM webuser WHERE email='$email' AND usertype='p'");
    if($check->num_rows == 0){
        echo "MISSING in webuser: {$email} ({$row['pname']})\n";
    } else {
        echo "OK: {$email}\n";
    }
}
?>

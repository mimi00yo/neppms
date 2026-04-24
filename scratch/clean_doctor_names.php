<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Cleaning 'Dr.' prefixes from doctor names...\n";

$res = $database->query("SELECT docid, docname FROM doctor");
$count = 0;
while($row = $res->fetch_assoc()){
    $id = $row['docid'];
    $old_name = $row['docname'];
    
    // Remove "Dr. " or "Dr." from the start (case insensitive)
    $new_name = preg_replace('/^Dr\.?\s+/i', '', $old_name);
    
    if($new_name !== $old_name){
        $database->query("UPDATE doctor SET docname = '$new_name' WHERE docid = $id");
        $count++;
    }
}

echo "Cleaned $count doctor names.\n";
?>

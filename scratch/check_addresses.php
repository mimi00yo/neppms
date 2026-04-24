<?php
include 'connection.php';

echo "--- Current Doctor Addresses (First 5) ---\n";
$res = $database->query("SELECT docname, docemail FROM doctor LIMIT 5");
// Note: doctor table doesn't seem to have an address column in some versions, let's check schema
$res_schema = $database->query("DESCRIBE doctor");
$has_address = false;
while($col = $res_schema->fetch_assoc()) {
    if ($col['Field'] == 'docaddress') $has_address = true;
}
if (!$has_address) echo "Doctor table does NOT have an address column.\n";

echo "\n--- Current Patient Addresses ---\n";
$res = $database->query("SELECT pname, paddress FROM patient");
while($row = $res->fetch_assoc()) {
    echo $row['pname'] . ": " . $row['paddress'] . "\n";
}
?>

<?php
include 'connection.php';

// 1. Get all doctor names for comparison
$doctors = [];
$res = $database->query("SELECT DISTINCT docname FROM doctor");
while($row = $res->fetch_assoc()) $doctors[] = strtolower(trim($row['docname']));

echo "--- Full Overlap Report ---\n";
$patients_res = $database->query("SELECT pid, pname, pemail FROM patient");
$match_count = 0;
while($row = $patients_res->fetch_assoc()) {
    $pname = strtolower(trim($row['pname']));
    if (in_array($pname, $doctors)) {
        echo "MATCH FOUND: ID " . $row['pid'] . " | " . $row['pname'] . " (" . $row['pemail'] . ")\n";
        $match_count++;
    }
}

echo "\nTotal matches found: $match_count\n";
echo "Total patients in DB: " . $patients_res->num_rows . "\n";
?>

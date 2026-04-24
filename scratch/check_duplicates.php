<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Checking for duplicate emails...\n";

// Doctors
$res = $database->query("SELECT docemail, COUNT(*) as c FROM doctor GROUP BY docemail HAVING c > 1");
while($row = $res->fetch_assoc()){
    echo "Duplicate Doctor Email: {$row['docemail']} ({$row['c']} times)\n";
}

// Patients
$res = $database->query("SELECT pemail, COUNT(*) as c FROM patient GROUP BY pemail HAVING c > 1");
while($row = $res->fetch_assoc()){
    echo "Duplicate Patient Email: {$row['pemail']} ({$row['c']} times)\n";
}

// Cross table
$res = $database->query("SELECT docemail FROM doctor INTERSECT SELECT pemail FROM patient");
if ($res) {
    while($row = $res->fetch_assoc()){
        echo "Cross-table duplicate: {$row['docemail']}\n";
    }
} else {
    // If INTERSECT is not supported
    $res = $database->query("SELECT docemail FROM doctor WHERE docemail IN (SELECT pemail FROM patient)");
    while($row = $res->fetch_assoc()){
        echo "Cross-table duplicate: {$row['docemail']}\n";
    }
}
?>

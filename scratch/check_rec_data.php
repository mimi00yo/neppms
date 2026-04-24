<?php
include 'connection.php';

// Check if reviews table exists and its structure
$res = $database->query("SHOW TABLES LIKE 'reviews'");
if ($res->num_rows > 0) {
    echo "--- Reviews Table ---\n";
    $desc = $database->query("DESCRIBE reviews");
    while($row = $desc->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n";
    
    echo "\n--- Sample Reviews ---\n";
    $reviews = $database->query("SELECT * FROM reviews LIMIT 5");
    while($row = $reviews->fetch_assoc()) print_r($row);
} else {
    echo "No reviews table found.\n";
}

echo "\n--- Doctor Table Columns ---\n";
$desc = $database->query("DESCRIBE doctor");
while($row = $desc->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n";

echo "\n--- Schedule Table Columns ---\n";
$desc = $database->query("DESCRIBE schedule");
while($row = $desc->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n";

echo "\n--- Appointment Counts per Doctor ---\n";
$res = $database->query("SELECT d.docname, d.docid, COUNT(a.appoid) as total_appointments, AVG(COALESCE(r.rating, 0)) as avg_rating FROM doctor d LEFT JOIN schedule s ON d.docid = s.docid LEFT JOIN appointment a ON s.scheduleid = a.scheduleid LEFT JOIN reviews r ON r.docid = d.docid GROUP BY d.docid ORDER BY total_appointments DESC LIMIT 10");
while($row = $res->fetch_assoc()) echo $row['docname'] . " | Appointments: " . $row['total_appointments'] . " | Avg Rating: " . round($row['avg_rating'], 1) . "\n";
?>

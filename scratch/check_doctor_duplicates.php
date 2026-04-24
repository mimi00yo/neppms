<?php
include 'connection.php';

echo "--- Duplicate Doctor Names ---\n";
$res = $database->query("SELECT docname, COUNT(*) as count FROM doctor GROUP BY docname HAVING count > 1 ORDER BY count DESC");
while($row = $res->fetch_assoc()) {
    echo $row['docname'] . ": " . $row['count'] . " records\n";
}

echo "\n--- Checking for Appointments/Sessions on Duplicates ---\n";
// We'll look at doctors who have more than 1 record and see if they have any sessions
$res = $database->query("SELECT d.docname, d.docid, d.docemail, (SELECT COUNT(*) FROM schedule s WHERE s.docid = d.docid) as sessions, (SELECT COUNT(*) FROM appointment a JOIN schedule s2 ON a.scheduleid = s2.scheduleid WHERE s2.docid = d.docid) as appointments FROM doctor d WHERE d.docname IN (SELECT docname FROM doctor GROUP BY docname HAVING COUNT(*) > 1) ORDER BY d.docname ASC");

while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['docid'] . " | " . $row['docname'] . " (" . $row['docemail'] . ") | Sessions: " . $row['sessions'] . " | Appointments: " . $row['appointments'] . "\n";
}
?>

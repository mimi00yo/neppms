<?php
include 'connection.php';

// Disable foreign key checks temporarily if needed, but we'll try to do it cleanly
$database->query("SET FOREIGN_KEY_CHECKS=0");

// Get all duplicate names
$names_res = $database->query("SELECT docname FROM doctor GROUP BY docname HAVING COUNT(*) > 1");
$names = [];
while($row = $names_res->fetch_assoc()) $names[] = $row['docname'];

foreach ($names as $name) {
    echo "Processing merge for: $name\n";
    
    // Find all IDs for this name
    $docs_res = $database->query("SELECT d.docid, d.docemail, (SELECT COUNT(*) FROM schedule s WHERE s.docid = d.docid) as activity FROM doctor d WHERE d.docname = '" . $database->real_escape_string($name) . "' ORDER BY activity DESC, d.docid ASC");
    
    $docs = [];
    while($row = $docs_res->fetch_assoc()) $docs[] = $row;
    
    if (count($docs) <= 1) continue;
    
    $primary = $docs[0];
    $primary_id = $primary['docid'];
    echo "  Primary ID: $primary_id (" . $primary['docemail'] . ")\n";
    
    for ($i = 1; $i < count($docs); $i++) {
        $dup = $docs[$i];
        $dup_id = $dup['docid'];
        $dup_email = $dup['docemail'];
        
        echo "  Merging Duplicate ID: $dup_id ($dup_email)...\n";
        
        // Update schedules to point to primary
        $database->query("UPDATE schedule SET docid = $primary_id WHERE docid = $dup_id");
        
        // Delete from doctor table
        $database->query("DELETE FROM doctor WHERE docid = $dup_id");
        
        // Delete from webuser table
        $database->query("DELETE FROM webuser WHERE email = '" . $database->real_escape_string($dup_email) . "'");
    }
}

$database->query("SET FOREIGN_KEY_CHECKS=1");
echo "\nMerge complete. All duplicates resolved.\n";
?>

<?php
include 'connection.php';

$search = ['Goma', 'Bikash'];
$tables = ['doctor', 'patient', 'schedule', 'appointment', 'webuser'];

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $query = "SELECT * FROM $table WHERE ";
    $clauses = [];
    $res = $database->query("DESCRIBE $table");
    while($col = $res->fetch_assoc()) {
        foreach ($search as $s) {
            $clauses[] = "`" . $col['Field'] . "` LIKE '%$s%'";
        }
    }
    $query .= implode(" OR ", $clauses);
    
    $result = $database->query($query);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "No matches.\n";
    }
}
?>

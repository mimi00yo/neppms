<?php
include("C:/xampp/htdocs/edoc/connection.php");
$result = $database->query("DESCRIBE schedule");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $database->error;
}
?>

<?php
include("C:/xampp/htdocs/edoc/connection.php");

echo "Checking for is_cancelled in schedule table...\n";
$check_col = $database->query("SHOW COLUMNS FROM `schedule` LIKE 'is_cancelled'");
if($check_col->num_rows == 0){
    echo "Adding is_cancelled column...\n";
    $res = $database->query("ALTER TABLE `schedule` ADD `is_cancelled` TINYINT(1) DEFAULT 0");
    if($res){
        echo "Successfully added is_cancelled column.\n";
    } else {
        echo "Error adding column: " . $database->error . "\n";
    }
} else {
    echo "is_cancelled column already exists.\n";
}

// Also check for other potential missing columns from recent updates
// Based on grep, there might be a 'reviews' table and 'timeslots' table
echo "Checking for reviews table...\n";
$res = $database->query("SHOW TABLES LIKE 'reviews'");
if($res->num_rows == 0){
    echo "Creating reviews table...\n";
    $sql = "CREATE TABLE `reviews` (
        `review_id` int(11) NOT NULL AUTO_INCREMENT,
        `appoid` int(11) DEFAULT NULL,
        `rating` int(1) DEFAULT NULL,
        `comment` text DEFAULT NULL,
        `review_date` date DEFAULT NULL,
        PRIMARY KEY (`review_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $database->query($sql);
}

echo "Checking for timeslots table...\n";
$res = $database->query("SHOW TABLES LIKE 'timeslots'");
if($res->num_rows == 0){
    echo "Creating timeslots table...\n";
    $sql = "CREATE TABLE `timeslots` (
        `slot_id` int(11) NOT NULL AUTO_INCREMENT,
        `scheduleid` int(11) DEFAULT NULL,
        `slot_time` time DEFAULT NULL,
        `is_booked` tinyint(1) DEFAULT 0,
        PRIMARY KEY (`slot_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $database->query($sql);
}

?>

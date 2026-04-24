<?php
session_start();

if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='p'){
    header("location: ../login.php");
    exit;
}

include("../connection.php");

$spec_id = isset($_GET['spec_id']) ? (int)$_GET['spec_id'] : 0;

if ($spec_id > 0) {
    // Greedy Choice: Find the absolute earliest session for this specialty that has space
    date_default_timezone_set('Asia/Kathmandu');
    $today = date('Y-m-d');
    
    $sql = "SELECT s.scheduleid 
            FROM schedule s 
            JOIN doctor d ON s.docid = d.docid 
            WHERE d.specialties = ? 
              AND s.scheduledate >= ? 
              AND (s.nop - (SELECT COUNT(*) FROM appointment a WHERE a.scheduleid = s.scheduleid)) > 0
            ORDER BY s.scheduledate ASC, s.scheduletime ASC 
            LIMIT 1";
            
    $stmt = $database->prepare($sql);
    $stmt->bind_param("is", $spec_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $scheduleid = $row['scheduleid'];
        // Redirect to booking wizard for this earliest slot
        header("location: booking.php?id=$scheduleid");
        exit;
    } else {
        // No slots found, redirect back with error
        header("location: specialties.php?error=no_slots");
        exit;
    }
} else {
    header("location: specialties.php");
    exit;
}
?>

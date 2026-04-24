<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){
        include("../connection.php");
        $id=$_GET["id"];
        
        // 1. Compatible Migration: Check if column exists first
        $check_col = $database->query("SHOW COLUMNS FROM `schedule` LIKE 'is_cancelled'");
        if ($check_col->num_rows == 0) {
            $database->query("ALTER TABLE `schedule` ADD `is_cancelled` TINYINT(1) DEFAULT 0");
        }

        // 2. Check if there are any appointments for this session
        $check_appo = $database->query("SELECT COUNT(*) as c FROM appointment WHERE scheduleid = '$id'");
        $appo_row = $check_appo->fetch_assoc();
        $appo_count = isset($appo_row['c']) ? (int)$appo_row['c'] : 0;

        if ($appo_count > 0) {
            // 3. SOFT DELETE: Mark as cancelled instead of deleting
            $database->query("UPDATE schedule SET is_cancelled = 1 WHERE scheduleid = '$id'");
        } else {
            // 4. PERMANENT DELETE: Only if no appointments exist
            $database->query("DELETE FROM schedule WHERE scheduleid = '$id'");
        }

        header("location: schedule.php");
        exit();
    }


?>
<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){
        include("../connection.php");
        $id=$_GET["id"];
        $stmt = $database->prepare("delete from appointment where appoid=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        header("location: appointment.php?action=cancelled");
    }


?>
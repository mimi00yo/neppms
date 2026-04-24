<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_POST){
        //import database
        include("../connection.php");
        $id=$_POST["id"];
        $title=$_POST["title"];
        $docid=$_POST["docid"];
        $nop=$_POST["nop"];
        $date=$_POST["date"];
        $time=$_POST["time"];
        
        $sql="update schedule set docid=$docid,title='$title',scheduledate='$date',scheduletime='$time',nop=$nop where scheduleid=$id;";
        $result= $database->query($sql);
        header("location: schedule.php?action=edit-success&title=$title");
        
    }


?>

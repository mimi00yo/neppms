<?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }

    if(isset($_GET["id"])){
        include("../connection.php");
        $id = (int)$_GET["id"];
        
        // Store the appoid in session to mark we are in "reschedule mode"
        $_SESSION['reschedule_id'] = $id;

        // Fetch original session/doctor info to guide the user back to selection
        $stmt = $database->prepare("SELECT schedule.docid FROM appointment INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid WHERE appointment.appoid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if($res){
            // Redirect to step 1 to let them pick any specialty/doctor, 
            // OR step 2 for the same doctor. Let's do step 1 for maximum flexibility.
            header("location: booking.php?step=1&reschedule=true");
        } else {
            header("location: appointment.php");
        }
    } else {
        header("location: appointment.php");
    }
?>

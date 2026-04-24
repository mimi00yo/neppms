<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Appointments</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .tab-container { display: flex; gap: 20px; margin-left: 45px; margin-top: 10px; border-bottom: 1px solid #eee; margin-bottom: 20px; }
        .tab-btn { padding: 12px 25px; cursor: pointer; border: none; background: none; font-size: 15px; font-weight: 600; color: #888; transition: 0.3s; position: relative; }
        .tab-btn:hover { color: var(--primarycolor); }
        .tab-btn.active { color: var(--primarycolor); }
        .tab-btn.active::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: var(--primarycolor); border-radius: 10px 10px 0 0; }
        .badge { background: #eee; color: #777; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: 5px; }
        .tab-btn.active .badge { background: var(--primarycolor); color: #fff; }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }
    

    //import database
    include("../connection.php");
    $sqlmain= "select * from patient where pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s",$useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];


    //echo $userid;
    //echo $username;


    //TODO
    if ($_POST && isset($_POST['submit_review'])) {
        $r_appoid = (int)$_POST['r_appoid'];
        $r_docid  = (int)$_POST['r_docid'];
        $r_rating = (int)$_POST['rating'];
        $r_text   = $_POST['review_text'];

        if ($r_rating >= 1 && $r_rating <= 5) {
            $stmt_r = $database->prepare("INSERT IGNORE INTO reviews (appoid, pid, docid, rating, review_text) VALUES (?, ?, ?, ?, ?)");
            $stmt_r->bind_param("iiiis", $r_appoid, $userid, $r_docid, $r_rating, $r_text);
            $stmt_r->execute();
            echo "<script>window.location.href='appointment.php?action=review-added';</script>";
            exit;
        }
    }

    // Auto-migrate if needed
    $database->query("ALTER TABLE schedule ADD COLUMN IF NOT EXISTS is_cancelled TINYINT(1) DEFAULT 0");

    $sqlmain= "select appointment.appoid, schedule.scheduleid, schedule.title, doctor.docid, doctor.docname, patient.pname, schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate, r.review_id, schedule.nop, doctor.doc_slot_duration, t.slot_time, schedule.is_cancelled from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join patient on patient.pid=appointment.pid inner join doctor on schedule.docid=doctor.docid left join reviews r on r.appoid=appointment.appoid left join timeslots t on appointment.slot_id = t.slot_id where patient.pid=$userid ";

    if($_POST && !isset($_POST['submit_review'])){
        if(!empty($_POST["scheduledate"])){
            $scheduledate=$_POST["scheduledate"];
            $sqlmain.=" and schedule.scheduledate='$scheduledate' ";
        };
    }

    $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
    date_default_timezone_set('Asia/Kathmandu');
    $today = date('Y-m-d');

    if($current_tab == 'upcoming'){
        $sql_final = $sqlmain . " AND schedule.scheduledate >= '$today' order by schedule.scheduledate asc";
    }else{
        $sql_final = $sqlmain . " AND schedule.scheduledate < '$today' order by schedule.scheduledate desc";
    }

    $result = $database->query($sql_final);

    // Get counts for badges
    $count_upcoming = $database->query($sqlmain . " AND schedule.scheduledate >= '$today'")->num_rows;
    $count_history = $database->query($sqlmain . " AND schedule.scheduledate < '$today'")->num_rows;
    ?>
    <div class="container">
        <div class="menu">
        <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-book" style="background-image: url('../img/icons/book.svg');">
                        <a href="quick-book.php" class="non-style-link-menu"><div><p class="menu-text" style="color:#0A76D8; font-weight:800;">⚡ Quick Booking</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-specialties">
                        <a href="specialties.php" class="non-style-link-menu"><div><p class="menu-text">Specialties</p></div></a>
                    </td>
                </tr>


                
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="booking.php?step=1" class="non-style-link-menu"><div><p class="menu-text">Step-by-Step Booking</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment  menu-active menu-icon-appoinment-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
                
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="appointment.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">My Bookings history</p>
                                           
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 

                        date_default_timezone_set('Asia/kathmandu');

                        $today = date('Y-m-d');
                        echo $today;

                        
                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
               
                <!-- <tr>
                    <td colspan="4" >
                        <div style="display: flex;margin-top: 40px;">
                        <div class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49);margin-top: 5px;">Schedule a Session</div>
                        <a href="?action=add-session&id=none&error=0" class="non-style-link"><button  class="login-btn btn-primary btn button-icon"  style="margin-left:25px;background-image: url('../img/icons/add.svg');">Add a Session</font></button>
                        </a>
                        </div>
                    </td>
                </tr> -->
                <tr>
                    <td colspan="4" style="padding-top:10px;width: 100%;" >
                        <div class="tab-container">
                            <button class="tab-btn <?php echo $current_tab == 'upcoming' ? 'active' : ''; ?>" onclick="window.location.href='?tab=upcoming'">Upcoming Bookings <span class="badge"><?php echo $count_upcoming; ?></span></button>
                            <button class="tab-btn <?php echo $current_tab == 'history' ? 'active' : ''; ?>" onclick="window.location.href='?tab=history'">Booking History <span class="badge"><?php echo $count_history; ?></span></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;" >
                        <center>
                        <table class="filter-container" border="0" >
                        <tr>
                           <td width="10%">

                           </td> 
                        <td width="5%" style="text-align: center;">
                        Date:
                        </td>
                        <td width="30%">
                        <form action="" method="post">
                            
                            <input type="date" name="scheduledate" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;">

                        </td>
                        
                    <td width="12%">
                        <input type="submit"  name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter"  style="padding: 15px; margin :0;width:100%">
                        </form>
                    </td>

                    </tr>
                            </table>

                        </center>
                    </td>
                    
                </tr>
                
               
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0" style="border:none">
                        
                        <tbody>
                        
                            <?php

                                
                                

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="7">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{

                                    for ( $x=0; $x<($result->num_rows);$x++){
                                        echo "<tr>";
                                        for($q=0;$q<3;$q++){
                                            $row=$result->fetch_assoc();
                                            if (!isset($row)){
                                            break;
                                            };
                                            $scheduleid=$row["scheduleid"];
                                            $title=$row["title"];
                                            $docid=$row["docid"];
                                            $docname=$row["docname"];
                                            $scheduledate=$row["scheduledate"];
                                            $scheduletime=$row["scheduletime"];
                                            $apponum=$row["apponum"];
                                            $appodate=$row["appodate"];
                                            $appoid=$row["appoid"];
                                            $review_id=$row["review_id"] ?? null;
                                            $slot_time = $row["slot_time"];
                                            $slot_duration = (int)($row["doc_slot_duration"] ?? 15);
                                            
                                            if (empty($slot_time)) {
                                                // Calculate time based on apponum
                                                $offset_mins = ($apponum - 1) * $slot_duration;
                                                $slot_time = date('H:i:s', strtotime("+$offset_mins minutes", strtotime($scheduletime)));
                                            }
                                            $formatted_time = date("h:i A", strtotime($slot_time));
                                               $is_cancelled = (int)($row["is_cancelled"] ?? 0);
    
                                            if($scheduleid==""){
                                                break;
                                            }
    
                                            date_default_timezone_set('Asia/Kathmandu');
                                            $is_past = ($scheduledate < date('Y-m-d'));
                                            
                                            if ($is_cancelled) {
                                                $act_btn = '
                                                <div style="display:flex; flex-direction:column; gap:8px;">
                                                    <a href="reschedule.php?id='.$appoid.'" ><button class="login-btn btn-primary btn" style="padding-top:11px;padding-bottom:11px;width:100%; background-color:#ef4444; border:none;"><font class="tn-in-text">Reschedule for Free &rarr;</font></button></a>
                                                    <p style="font-size:11px; color:#ef4444; font-weight:600; text-align:center; margin:0;">The doctor cancelled this session.</p>
                                                </div>';
                                            } else if ($is_past) {
                                                if ($review_id) {
                                                    $act_btn = '<button title="Review already submitted" class="login-btn btn-primary-soft btn" disabled style="padding-top:11px;padding-bottom:11px;width:100%;color:#16a085;border-color:#16a085;cursor:not-allowed;">&#10004; Reviewed</button>';
                                                } else {
                                                    $act_btn = '<a href="?action=rate&id='.$appoid.'&docid='.$docid.'&docname='.urlencode($docname).'" ><button class="login-btn btn-primary btn" style="padding-top:11px;padding-bottom:11px;width:100%;background-color:#f39c12;border:none;"><font class="tn-in-text">&#9733; Rate Doctor</font></button></a>';
                                                }
                                            } else {
                                                $act_btn = '
                                                <div style="display:flex; flex-direction:column; gap:8px;">
                                                    <a href="reschedule.php?id='.$appoid.'" ><button class="login-btn btn-primary btn" style="padding-top:11px;padding-bottom:11px;width:100%;"><font class="tn-in-text">Reschedule Booking</font></button></a>
                                                    <p style="font-size:10px; color:#888; text-align:center; margin:0;">* No cancellations or refunds allowed.</p>
                                                </div>';
                                            }

                                            // Override card content if cancelled
                                            $card_content = '
                                                <div class="h3-search">
                                                    Booking Date: '.substr($appodate,0,30).'<br>
                                                    Reference Number: OC-000-'.$appoid.'
                                                </div>
                                                <div class="h1-search">
                                                    '.substr($title,0,21).'<br>
                                                </div>';

                                            if ($is_cancelled) {
                                                $card_content .= '
                                                <div style="background:#fef2f2; border:1px dashed #ef4444; padding:20px; border-radius:10px; margin:15px 0; text-align:center;">
                                                    <p style="color:#ef4444; font-weight:700; font-size:16px; margin:0;">🛑 SESSION CANCELLED</p>
                                                    <p style="color:#7f1d1d; font-size:12px; margin-top:5px;">This session was cancelled by the doctor. Please use your credit to reschedule below.</p>
                                                </div>';
                                            } else {
                                                $card_content .= '
                                                <div style="display:flex; justify-content:space-between; align-items:center; background:#f0f7ff; padding:15px; border-radius:10px; margin:15px 0;">
                                                    <div class="h3-search" style="margin:0;">
                                                        Appointment Number:<br>
                                                        <div class="h1-search" style="font-size:35px; line-height:1;">'.str_pad($apponum, 2, '0', STR_PAD_LEFT).'</div>
                                                    </div>
                                                    <div style="text-align:right;">
                                                        <span style="color:#0062cc; font-size:12px; font-weight:700; text-transform:uppercase;">Scheduled Time</span><br>
                                                        <div style="font-size:24px; font-weight:800; color:#333;">@'.$formatted_time.'</div>
                                                    </div>
                                                </div>
                                                <div class="h3-search">
                                                    Doctor: <b>'.substr($docname,0,30).'</b>
                                                </div>
                                                <div class="h4-search" style="margin-top:10px; color:#666;">
                                                    Scheduled Date: <b>'.$scheduledate.'</b><br>
                                                    Session Starts At: <b>'.date("h:i A", strtotime($scheduletime)).'</b>
                                                </div>';
                                            }
                                            
                                            echo '
                                            <td style="width: 25%;">
                                                    <div  class="dashboard-items search-items"  >
                                                        <div style="width:100%;">
                                                                '.$card_content.'
                                                                <br>
                                                                '.$act_btn.'
                                                        </div>
                                                    </div>
                                                </td>';
    
                                        }
                                        echo "</tr>";
                                //     $scheduleid=$row["scheduleid"];
                                //     $title=$row["title"];
                                //     $docname=$row["docname"];
                                //     $scheduledate=$row["scheduledate"];
                                //     $scheduletime=$row["scheduletime"];
                                //     $pname=$row["pname"];
                                //     
                                //     
                                //     echo '<tr >
                                //         <td style="font-weight:600;"> &nbsp;'.
                                        
                                //         substr($pname,0,25)
                                //         .'</td >
                                //         <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                //         '.$apponum.'
                                        
                                //         </td>
                                //         <td>
                                //         '.substr($title,0,15).'
                                //         </td>
                                //         <td style="text-align:center;;">
                                //             '.substr($scheduledate,0,10).' @'.substr($scheduletime,0,5).'
                                //         </td>
                                        
                                //         <td style="text-align:center;">
                                //             '.$appodate.'
                                //         </td>

                                //         <td>
                                //         <div style="display:flex;justify-content: center;">
                                        
                                //         <!--<a href="?action=view&id='.$appoid.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                //        &nbsp;&nbsp;&nbsp;-->
                                //        <a href="?action=drop&id='.$appoid.'&name='.$pname.'&session='.$title.'&apponum='.$apponum.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Cancel</font></button></a>
                                //        &nbsp;&nbsp;&nbsp;</div>
                                //         </td>
                                //     </tr>';
                                    
                                }
                            }
                                 
                            ?>
 
                            </tbody>

                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
                       
                        
                        
            </table>
        </div>
    </div>
    <?php
    
    if($_GET){
        $id=isset($_GET["id"]) ? $_GET["id"] : '';
        $action=isset($_GET["action"]) ? $_GET["action"] : '';
        if($action=='booking-rescheduled'){
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Reschedule Successful!</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                            Your booking has been moved to the new time slot.<br>
                            Your previous payment has been successfully applied.<br><br>
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="appointment.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='booking-added'){
            
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Booking Successfully.</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                        Your Appointment number is '.$id.'.<br><br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        
                        <a href="appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='drop'){
            $title=$_GET["title"];
            $docname=$_GET["doc"];
            
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                            You want to Cancel this Appointment?<br><br>
                            Session Name: &nbsp;<b>'.substr($title,0,40).'</b><br>
                            Doctor name&nbsp; : <b>'.substr($docname,0,40).'</b><br><br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="delete-appointment.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            '; 
        }elseif($action=='cancelled'){
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Booking Cancelled.</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                            The appointment has been successfully removed.<br><br>
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="appointment.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='review-added'){
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Review Submitted!</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                            Thank you for your feedback.<br><br>
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="appointment.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='rate'){
            $docid = $_GET["docid"];
            $docname = urldecode($_GET["docname"]);
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup" style="padding:30px;">
                    <center>
                        <h2>Rate Your Visit</h2>
                        <a class="close" href="appointment.php">&times;</a>
                        <div class="content">
                            Doctor: &nbsp;<b>'.htmlspecialchars($docname).'</b><br><br>
                            <form action="appointment.php" method="POST">
                                <input type="hidden" name="submit_review" value="1">
                                <input type="hidden" name="r_appoid" value="'.$id.'">
                                <input type="hidden" name="r_docid" value="'.$docid.'">
                                
                                <label style="font-weight:600;">Star Rating (1-5):</label><br>
                                <select name="rating" class="input-text" style="width:100%; text-align:center; padding:10px; font-size:18px;" required>
                                    <option value="5" selected>&#9733;&#9733;&#9733;&#9733;&#9733; - Excellent</option>
                                    <option value="4">&#9733;&#9733;&#9733;&#9733; - Good</option>
                                    <option value="3">&#9733;&#9733;&#9733; - Average</option>
                                    <option value="2">&#9733;&#9733; - Poor</option>
                                    <option value="1">&#9733; - Terrible</option>
                                </select><br><br>
                                
                                <label style="font-weight:600;">Written Review:</label><br>
                                <textarea name="review_text" class="input-text" rows="4" style="width:100%; padding:10px;" placeholder="Describe your experience..." required></textarea>
                                
                                <br><br>
                                <button type="submit" class="btn-primary btn" style="width:100%; padding:15px; font-size:16px;">Submit Review</button>
                            </form>
                        </div>
                    </center>
            </div>
            </div>
            '; 
        }elseif($action=='view'){
            $sqlmain= "select * from doctor where docid=?";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row=$result->fetch_assoc();
            $name=$row["docname"];
            $email=$row["docemail"];
            $spe=$row["specialties"];
            
            $sqlmain= "select sname from specialties where id=?";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("s",$spe);
            $stmt->execute();
            $spcil_res = $stmt->get_result();
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name=$spcil_array["sname"];
            $tele=$row['doctel'];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="doctors.php">&times;</a>
                        <div class="content">
                            eDoc Web App<br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$name.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$email.'<br><br>
                                </td>
                            </tr>

                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Specialties: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$spcil_name.'<br><br>
                            </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="doctors.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';  
    }
}

    ?>
    </div>

</body>
</html>

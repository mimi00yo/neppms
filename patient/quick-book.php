<?php
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

include("../connection.php");

$userrow = $database->query("select * from patient where pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["pid"];
$username=$userfetch["pname"];

date_default_timezone_set('Asia/Kathmandu');
$today = date('Y-m-d');

// ── Handle the "Find Earliest" logic ────────────────────────────────────────
if (isset($_GET['spec_id']) && (int)$_GET['spec_id'] > 0) {
    $spec_id = (int)$_GET['spec_id'];
    
    // Find sessions for this specialty, ordered by date and time
    $sql = "
        SELECT s.scheduleid, s.scheduledate, s.scheduletime, d.docname, s.nop, d.doc_slot_duration, s.docid
        FROM schedule s
        JOIN doctor d ON s.docid = d.docid
        WHERE d.specialties = ? AND s.scheduledate >= ? AND s.is_cancelled = 0
        ORDER BY s.scheduledate ASC, s.scheduletime ASC
        LIMIT 50
    ";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("is", $spec_id, $today);
    
    $stmt->execute();
    $sessions = $stmt->get_result();
    
    $best_sid = 0;
    $best_slot_id = 0;
    $best_slot_num = 0;

    while ($session = $sessions->fetch_assoc()) {
        $sid = $session['scheduleid'];
        
        // 1. Check for named timeslots first
        $ts_stmt = $database->prepare("SELECT slot_id, slot_number FROM timeslots WHERE scheduleid = ? AND is_booked = 0 ORDER BY slot_time ASC LIMIT 1");
        $ts_stmt->bind_param("i", $sid);
        $ts_stmt->execute();
        $ts_res = $ts_stmt->get_result();
        
        if ($ts_row = $ts_res->fetch_assoc()) {
            $best_sid = $sid;
            $best_slot_id = $ts_row['slot_id'];
            $best_slot_num = $ts_row['slot_number'];
            $ts_stmt->close();
            break; // Found the earliest!
        }
        $ts_stmt->close();
        
        // 2. Fallback: Calculated token path
        $appo_stmt = $database->prepare("SELECT COUNT(*) as c FROM appointment WHERE scheduleid = ?");
        $appo_stmt->bind_param("i", $sid);
        $appo_stmt->execute();
        $booked_count = $appo_stmt->get_result()->fetch_assoc()['c'];
        $appo_stmt->close();
        
        if ($booked_count < $session['nop']) {
            $best_sid = $sid;
            $best_slot_id = 0;
            
            // Find first gap
            $gap_stmt = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ? ORDER BY apponum ASC");
            $gap_stmt->bind_param("i", $sid);
            $gap_stmt->execute();
            $gap_res = $gap_stmt->get_result();
            $booked_tokens = [];
            while($g = $gap_res->fetch_assoc()) $booked_tokens[] = (int)$g['apponum'];
            $gap_stmt->close();
            
            $best_slot_num = 1;
            while(in_array($best_slot_num, $booked_tokens)) $best_slot_num++;
            
            break; // Found the earliest!
        }
    }

    if ($best_sid > 0) {
        header("Location: booking.php?step=3&id=$best_sid&slot_id=$best_slot_id&slot_num=$best_slot_num&spec=$spec_id");
        exit;
    } else {
        $error = "No available slots found in the near future.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Quick Booking</title>
    <style>
        :root {
            --primary: #0A76D8;
            --primary-soft: #D8EBFA;
            --bg-cute: #f9fafb;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--bg-cute);
        }

        .dash-body {
            padding: 30px !important;
        }

        .quick-header {
            background: linear-gradient(135deg, #0A76D8 0%, #3b82f6 100%);
            padding: 40px;
            border-radius: 24px;
            color: #fff;
            margin-bottom: 30px;
            box-shadow: 0 15px 30px -5px rgba(10, 118, 216, 0.3);
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }
        .spec-card {
            background: #fff;
            padding: 35px 25px;
            border-radius: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--card-shadow);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            border: 1px solid transparent;
        }
        .spec-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .spec-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-soft);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 24px;
        }
        .spec-name {
            font-weight: 800;
            color: #1e1b4b;
            font-size: 18px;
        }
    </style>
</head>
<body>
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
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-book menu-active menu-icon-book-active" style="background-image: url('../img/icons/book.svg');">
                        <a href="quick-book.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text" style="color:white; font-weight:800;">⚡ Quick Booking</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="booking.php?step=1" class="non-style-link-menu"><div><p class="menu-text">Step-by-Step Booking</p></div></a>
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
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr>
                    <td width="13%" >
                    <a href="index.php" ><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Quick Booking</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo $today; ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4">
                        <div style="padding:20px;">
                            <div class="quick-header">
                                <h1 style="margin:0; font-size:32px;">Need a doctor ASAP?</h1>
                                <p style="margin:10px 0 0 0; opacity:0.9;">Choose a specialty below. We'll instantly find and book the very next available slot for you.</p>
                            </div>

                            <?php if (isset($error)): ?>
                                <div style="background:#fee2e2; border-left:4px solid #ef4444; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px;">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <div class="spec-grid">
                                 <?php
                                $specs = $database->query("SELECT * FROM specialties ORDER BY sname ASC");
                                while($s = $specs->fetch_assoc()):
                                ?>
                                <a href="?spec_id=<?php echo $s['id']; ?>" class="spec-card">
                                    <div class="spec-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                    </div>
                                    <div class="spec-name"><?php echo $s['sname']; ?></div>
                                    <div style="font-size:12px; color:var(--primary-color); font-weight:700;">FIND EARLIEST &rarr;</div>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

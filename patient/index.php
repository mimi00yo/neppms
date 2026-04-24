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

$sqlmain= "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();

$userid= $userfetch["pid"];
$username=$userfetch["pname"];

date_default_timezone_set('Asia/Kathmandu');
$today = date('Y-m-d');

$patientrow = $database->query("select * from appointment where pid='$userid';");
$doctorrow = $database->query("select * from doctor;");
$appointmentrow = $database->query("select * from appointment where pid='$userid' and appodate>='$today';");
$schedulerow = $database->query("select * from appointment inner join schedule on appointment.scheduleid=schedule.scheduleid where appointment.pid='$userid' and schedule.scheduledate='$today';");

// --- Recommendation Logic ---
$recommended_doctors = [];
$rec_type = "Personalized";
$fav_specs_sql = "SELECT d.specialties FROM appointment a JOIN schedule sch ON a.scheduleid = sch.scheduleid JOIN doctor d ON sch.docid = d.docid WHERE a.pid = ? GROUP BY d.specialties ORDER BY COUNT(*) DESC LIMIT 3";
$stmt_fav = $database->prepare($fav_specs_sql);
$stmt_fav->bind_param("i", $userid);
$stmt_fav->execute();
$fav_res = $stmt_fav->get_result();
$fav_specs = [];
while($fs = $fav_res->fetch_assoc()) $fav_specs[] = $fs['specialties'];

if (!empty($fav_specs)) {
    $spec_placeholders = implode(',', array_fill(0, count($fav_specs), '?'));
    $doc_sql = "SELECT d.*, s.sname FROM doctor d JOIN specialties s ON d.specialties = s.id JOIN (SELECT docid, MIN(scheduledate) as next_session FROM schedule WHERE scheduledate >= ? GROUP BY docid) sch ON d.docid = sch.docid WHERE d.specialties IN ($spec_placeholders) AND d.docid NOT IN (SELECT DISTINCT sch2.docid FROM appointment a2 JOIN schedule sch2 ON a2.scheduleid = sch2.scheduleid WHERE a2.pid = ?) GROUP BY d.specialties LIMIT 3";
    $stmt_docs = $database->prepare($doc_sql);
    $types = "s" . str_repeat("i", count($fav_specs)) . "i";
    $params = array_merge([$today], $fav_specs, [$userid]);
    $stmt_docs->bind_param($types, ...$params);
    $stmt_docs->execute();
    $recommended_doctors = $stmt_docs->get_result()->fetch_all(MYSQLI_ASSOC);
}
if (count($recommended_doctors) < 3) {
    $rec_type = "Medical Discovery";
    $exclude_ids = !empty($recommended_doctors) ? array_column($recommended_doctors, 'docid') : [0];
    $exclude_placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
    $pop_sql = "SELECT d.*, s.sname FROM doctor d JOIN specialties s ON d.specialties = s.id LEFT JOIN (SELECT sch.docid, COUNT(a.appoid) as appo_count FROM schedule sch LEFT JOIN appointment a ON sch.scheduleid = a.scheduleid GROUP BY sch.docid) stats ON d.docid = stats.docid WHERE d.docid NOT IN ($exclude_placeholders) GROUP BY d.specialties ORDER BY stats.appo_count DESC LIMIT " . (3 - count($recommended_doctors));
    $stmt_pop = $database->prepare($pop_sql);
    $stmt_pop->bind_param(str_repeat("i", count($exclude_ids)), ...$exclude_ids);
    $stmt_pop->execute();
    $more_docs = $stmt_pop->get_result()->fetch_all(MYSQLI_ASSOC);
    $recommended_doctors = array_merge($recommended_doctors, $more_docs);
}

// --- Health Tips Logic ---
$tips_title = "General Wellness";
$exercise_tip = "Stay active! Aim for 30 minutes of brisk walking.";
$food_tip = "Stay hydrated! Drink 8 glasses of water today.";
$tip_icon = "💡";
$latest_booking_res = $database->query("select specialties.sname, schedule.title from appointment inner join schedule on appointment.scheduleid=schedule.scheduleid inner join doctor on schedule.docid=doctor.docid inner join specialties on doctor.specialties=specialties.id where appointment.pid='$userid' and schedule.scheduledate>='$today' order by schedule.scheduledate asc limit 1");
if($latest_booking_res && $latest_booking_res->num_rows>0){
    $latest_booking = $latest_booking_res->fetch_assoc();
    $specialty = strtolower($latest_booking['sname']);
    if(strpos($specialty, 'dent') !== false) { $tips_title = "Dental Care"; $exercise_tip = "Rest well today."; $food_tip = "Stick to soft, cool foods."; $tip_icon = "🦷"; }
    elseif(strpos($specialty, 'cardi') !== false) { $tips_title = "Heart Health"; $exercise_tip = "Light walking only."; $food_tip = "Focus on low-sodium meals."; $tip_icon = "❤️"; }
    elseif(strpos($specialty, 'derm') !== false) { $tips_title = "Skin Care"; $exercise_tip = "Gentle yoga is great."; $food_tip = "Antioxidant-rich berries."; $tip_icon = "✨"; }
    elseif(strpos($specialty, 'ortho') !== false) { $tips_title = "Bone Health"; $exercise_tip = "Low-impact stretching."; $food_tip = "Increase Calcium & Vit D."; $tip_icon = "🦴"; }
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
    <link rel="stylesheet" href="../css/recommendation.css">
    <title>Dashboard</title>
    <style>
        :root {
            --primary: #0A76D8;
            --primary-soft: #D8EBFA;
            --bg-cute: #f9fafb;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .dash-body {
            padding: 30px !important;
            background: var(--bg-cute);
        }

        .welcome-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 800;
            color: #1e1b4b;
            margin: 0;
        }

        .welcome-text p {
            color: #64748b;
            margin: 5px 0 0 0;
            font-size: 15px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .sidebar-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .cute-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.02);
            transition: transform 0.3s ease;
        }

        .cute-card:hover {
            transform: translateY(-5px);
        }

        .status-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .status-mini-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--card-shadow);
        }

        .status-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .status-info h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
        }

        .status-info p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }

        .health-tip-hero {
            background: linear-gradient(135deg, #0A76D8, #3b82f6);
            color: white;
            padding: 30px;
            border-radius: 24px;
            display: flex;
            gap: 25px;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .health-tip-hero::after {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .tip-icon-big {
            font-size: 50px;
            background: rgba(255,255,255,0.2);
            width: 90px;
            height: 90px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tip-content-hero h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 800;
        }

        .tip-details {
            display: flex;
            gap: 40px;
        }

        .tip-detail-item p:first-child {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .tip-detail-item p:last-child {
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            color: #1e1b4b;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        @media (max-width: 1100px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .status-row { grid-template-columns: 1fr 1fr; }
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
                                <td width="30%" style="padding-left:20px" ><img src="../img/user.png" alt="" width="100%" style="border-radius:50%"></td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13) ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22) ?></p>
                                </td>
                            </tr>
                            <tr><td colspan="2"><a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a></td></tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row" ><td class="menu-btn menu-icon-home menu-active menu-icon-home-active" ><a href="index.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Home</p></a></div></td></tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-book" style="background-image: url('../img/icons/book.svg');">
                        <a href="quick-book.php" class="non-style-link-menu"><div><p class="menu-text" style="color:var(--primary); font-weight:800;">⚡ Quick Booking</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-doctor"><a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-session"><a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-session"><a href="booking.php?step=1" class="non-style-link-menu"><div><p class="menu-text">Step-by-Step Booking</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-appoinment"><a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div></td></tr>
                <tr class="menu-row" ><td class="menu-btn menu-icon-settings"><a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div></td></tr>
            </table>
        </div>

        <div class="dash-body">
            <div class="welcome-header">
                <div class="welcome-text">
                    <h1>Welcome back, <?php echo $username; ?>! ✨</h1>
                    <p>Here's what's happening with your health journey today.</p>
                </div>
                <div class="date-badge" style="background: white; padding: 12px 24px; border-radius: 15px; box-shadow: var(--card-shadow); text-align: right;">
                    <p style="margin: 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Today's Date</p>
                    <p style="margin: 0; font-size: 16px; font-weight: 800; color: var(--primary);"><?php echo $today; ?></p>
                </div>
            </div>

            <div class="status-row">
                <div class="status-mini-card">
                    <div class="status-icon">👨‍⚕️</div>
                    <div class="status-info"><h4><?php echo $doctorrow->num_rows ?></h4><p>All Doctors</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">📋</div>
                    <div class="status-info"><h4><?php echo $patientrow->num_rows ?></h4><p>My Visits</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">📅</div>
                    <div class="status-info"><h4><?php echo $appointmentrow->num_rows ?></h4><p>My Bookings</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">⏱️</div>
                    <div class="status-info"><h4><?php echo $schedulerow->num_rows ?></h4><p>My Today</p></div>
                </div>
            </div>

            <div class="quick-actions-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <a href="quick-book.php" class="cute-card" style="text-decoration: none; background: linear-gradient(135deg, #0A76D8, #3b82f6); color: white; display: flex; align-items: center; gap: 20px; border: none;">
                    <div style="font-size: 30px; background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">⚡</div>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800;">Quick Booking</h3>
                        <p style="margin: 2px 0 0 0; opacity: 0.9; font-size: 13px;">Find the earliest slot ASAP</p>
                    </div>
                </a>
                <a href="booking.php?step=1" class="cute-card" style="text-decoration: none; border: 2px solid var(--primary-soft); display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 30px; background: var(--primary-soft); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">📅</div>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #1e1b4b;">Step-by-Step Booking</h3>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">Browse sessions and pick a time</p>
                    </div>
                </a>
            </div>

            <div class="health-tip-hero">
                <div class="tip-icon-big"><?php echo $tip_icon; ?></div>
                <div class="tip-content-hero">
                    <h3>Daily Health Tip: <?php echo $tips_title; ?></h3>
                    <div class="tip-details">
                        <div class="tip-detail-item"><p>Exercise</p><p><?php echo $exercise_tip; ?></p></div>
                        <div class="tip-detail-item"><p>Diet & Nutrition</p><p><?php echo $food_tip; ?></p></div>
                    </div>
                </div>
            </div>

            <br><br>

            <div class="dashboard-grid">
                <div class="main-content">
                    <div class="section-header">
                        <h2>Your Upcoming Bookings</h2>
                        <a href="appointment.php" class="view-all">View All →</a>
                    </div>
                    <div class="cute-card" style="padding: 0; overflow: hidden;">
                        <table width="100%" class="sub-table" style="border: none; margin: 0;">
                            <thead>
                                <tr>
                                    <th class="table-headin">Appo. No.</th>
                                    <th class="table-headin">Session Title</th>
                                    <th class="table-headin">Doctor</th>
                                    <th class="table-headin">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $safe_userid = !empty($userid) ? $userid : 0;
                                $sqlmain= "select appointment.appoid,schedule.scheduleid,schedule.title,doctor.docname,patient.pname,schedule.scheduledate,schedule.scheduletime,appointment.apponum,appointment.appodate from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join patient on patient.pid=appointment.pid inner join doctor on schedule.docid=doctor.docid where patient.pid='$safe_userid' and schedule.scheduledate>='$today' order by schedule.scheduledate asc limit 5";
                                $result= $database->query($sqlmain);
                                if($result->num_rows==0){
                                    echo '<tr><td colspan="4" style="text-align:center;padding:40px;color:#64748b;">No upcoming bookings found. <br><a href="schedule.php" style="color:var(--primary); font-weight:700;">Book one now!</a></td></tr>';
                                } else {
                                    while($row=$result->fetch_assoc()){
                                        echo '<tr>
                                            <td style="text-align:center; font-weight:700; color:var(--primary);">#'.$row["apponum"].'</td>
                                            <td style="font-weight:600;">'.substr($row["title"],0,30).'</td>
                                            <td>'.$row["docname"].'</td>
                                            <td style="text-align:center;">'.$row["scheduledate"].'<br>'.substr($row["scheduletime"],0,5).'</td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="sidebar-content">
                    <div class="section-header">
                        <h2>Recommended</h2>
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo $rec_type; ?></span>
                    </div>
                    <div class="rec-stack" style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($recommended_doctors as $rec_doc): ?>
                            <a href="booking.php?step=2&doc_search=<?php echo urlencode($rec_doc['docname']); ?>&spec=<?php echo $rec_doc['specialties']; ?>" class="cute-card" style="text-decoration: none; display: flex; align-items: center; gap: 15px; padding: 15px;">
                                <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--primary-soft); display: flex; align-items: center; justify-content: center; font-size: 20px;">👨‍⚕️</div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0; color: #1e1b4b; font-size: 15px;"><?php echo $rec_doc['docname']; ?></h4>
                                    <p style="margin: 2px 0 0 0; color: #64748b; font-size: 12px; font-weight: 600;"><?php echo $rec_doc["sname"]; ?></p>
                                </div>
                                <div style="color: #10b981; font-weight: 800; font-size: 18px;">+</div>
                            </a>
                        <?php endforeach; ?>
                        <a href="doctors.php" class="cute-card" style="text-align: center; border: 2px dashed #e2e8f0; background: transparent; box-shadow: none; color: #64748b; font-weight: 700; padding: 15px;">
                            See All Doctors
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
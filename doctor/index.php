<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");
$userrow = $database->query("select doctor.*, specialties.sname from doctor inner join specialties on doctor.specialties=specialties.id where docemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["docid"];
$username=$userfetch["docname"];
$spec_name=$userfetch["sname"];
$profile_img = !empty($userfetch['profile_image']) ? '../' . $userfetch['profile_image'] : '../img/user.png';

date_default_timezone_set('Asia/kathmandu');
$today = date('Y-m-d');

$patientrow = $database->query("select DISTINCT patient.pid from patient inner join appointment on patient.pid=appointment.pid inner join schedule on appointment.scheduleid=schedule.scheduleid where schedule.docid='$userid'");
$doctorrow = $database->query("select * from doctor;");
$appointmentrow = $database->query("select appointment.appoid from appointment inner join schedule on appointment.scheduleid=schedule.scheduleid where schedule.docid='$userid' and schedule.scheduledate>='$today' and schedule.is_cancelled=0;");
$schedulerow = $database->query("select * from schedule where docid='$userid' and scheduledate='$today' and is_cancelled=0;");
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
    <title>Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #0A76D8;
            --primary-soft: #D8EBFA;
            --bg-cute: #f9fafb;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-cute); margin: 0; }
        
        .dash-body { padding: 30px !important; }
        
        .header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .header-title h1 { margin: 0; font-size: 24px; color: #1e1b4b; font-weight: 800; }
        
        .welcome-card {
            background: #fff;
            padding: 35px;
            border-radius: 24px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
            background-image: linear-gradient(to right, #fff, var(--primary-soft));
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-content { flex: 1; }
        .welcome-card h3 { margin: 0; color: var(--primary); font-size: 18px; font-weight: 700; }
        .welcome-card h1 { margin: 10px 0; font-size: 32px; color: #1e1b4b; font-weight: 800; }
        .welcome-card p { margin-bottom: 20px; color: #64748b; line-height: 1.6; max-width: 600px; }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .status-mini-card {
            background: #fff;
            padding: 25px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: transform 0.3s ease;
            border: 1px solid #f1f5f9;
        }
        .status-mini-card:hover { transform: translateY(-5px); }
        .status-icon {
            width: 55px; height: 55px;
            background: var(--primary-soft);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: var(--primary);
        }
        .status-info h4 { margin: 0; font-size: 22px; color: #1e1b4b; font-weight: 800; }
        .status-info p { margin: 2px 0 0 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; }

        .cute-card {
            background: #fff;
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
            height: 100%;
        }
        .section-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .section-header h2 { font-size: 19px; color: #1e1b4b; font-weight: 800; margin: 0; }
        
        .sub-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        .sub-table thead th { padding: 12px; color: #94a3b8; font-size: 12px; font-weight: 700; text-transform: uppercase; text-align: left; }
        .sub-table tbody tr { background: #f8fafc; border-radius: 12px; transition: all 0.2s; }
        .sub-table tbody tr:hover { background: #f1f5f9; }
        .sub-table td { padding: 15px 12px; font-size: 14px; color: #334155; font-weight: 500; }
        .sub-table td:first-child { border-radius: 12px 0 0 12px; padding-left: 20px; }
        .sub-table td:last-child { border-radius: 0 12px 12px 0; padding-right: 20px; }

        .dashboard-main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .avatar-main { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
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
                                    <img src="<?php echo $profile_img ?>" alt="" width="100%" style="border-radius:50%; aspect-ratio: 1/1; object-fit: cover;">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13) ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22) ?></p>
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
                <tr class="menu-row"><td class="menu-btn menu-icon-dashbord menu-active menu-icon-dashbord-active"><a href="index.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Dashboard</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-appoinment"><a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-session"><a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">My Sessions</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-patient"><a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-settings"><a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></div></a></td></tr>
            </table>
        </div>
        <div class="dash-body">
            <div class="header-row">
                <div class="header-title">
                    <h1>Dashboard</h1>
                </div>
                <div style="text-align: right;">
                    <p style="margin:0; color:#64748b; font-size:12px; font-weight:600; text-transform: uppercase;">Today's Date</p>
                    <h3 style="margin:0; color:var(--primary); font-weight:800; font-size: 16px;"><?php echo $today; ?></h3>
                </div>
            </div>

            <div class="welcome-card">
                <div class="welcome-content">
                    <h3>Welcome!</h3>
                    <h1>Dr. <?php echo $username ?>.</h1>
                    <p>Specialist in <b><?php echo $spec_name ?></b>. Thanks for joining with us. We are always trying to get you a complete service. You can view your daily schedule and reach patients' appointments right from here!</p>
                    <a href="appointment.php" class="non-style-link"><button class="btn-primary btn" style="padding: 12px 30px; border-radius: 12px;">View My Appointments</button></a>
                </div>
                <img src="<?php echo $profile_img ?>" class="avatar-main" style="width: 120px; height: 120px; border-radius: 24px;">
            </div>

            <div class="dashboard-main-grid">
                <div>
                    <div class="section-header"><h2>Status Overview</h2></div>
                    <div class="status-grid">
                        <div class="status-mini-card">
                            <div class="status-icon">👨‍⚕️</div>
                            <div class="status-info"><h4><?php echo $doctorrow->num_rows ?></h4><p>Total Doctors</p></div>
                        </div>
                        <div class="status-mini-card">
                            <div class="status-icon">👥</div>
                            <div class="status-info"><h4><?php echo $patientrow->num_rows ?></h4><p>My Patients</p></div>
                        </div>
                        <div class="status-mini-card">
                            <div class="status-icon">📅</div>
                            <div class="status-info"><h4><?php echo $appointmentrow->num_rows ?></h4><p>Upcoming Bookings</p></div>
                        </div>
                        <div class="status-mini-card">
                            <div class="status-icon">⏱️</div>
                            <div class="status-info"><h4><?php echo $schedulerow->num_rows ?></h4><p>Today's Sessions</p></div>
                        </div>
                    </div>

                    <div class="cute-card" style="margin-top: 25px; border: 1px solid #fee2e2;">
                        <div class="section-header"><h2 style="color: #991b1b;">Recent Cancellations</h2></div>
                        <div style="overflow: auto; max-height: 200px;">
                            <table class="sub-table">
                                <tbody>
                                    <?php
                                    $sql_c = "select * from schedule where docid='$userid' and is_cancelled=1 order by scheduledate desc LIMIT 3";
                                    $res_c = $database->query($sql_c);
                                    if($res_c->num_rows==0){
                                        echo '<tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b;">No recent cancellations.</td></tr>';
                                    } else {
                                        while($row_c=$res_c->fetch_assoc()){
                                            echo '<tr style="background: #fff5f5;">
                                                <td style="color: #991b1b;">'.substr($row_c["title"],0,20).'</td>
                                                <td style="color: #991b1b;">'.$row_c["scheduledate"].'</td>
                                                <td style="color: #991b1b; font-weight:600;">'.substr($row_c["scheduletime"],0,5).'</td>
                                            </tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="cute-card">
                    <div class="section-header">
                        <h2>My Upcoming Sessions</h2>
                        <a href="schedule.php" class="view-all">View All →</a>
                    </div>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Overview for the next 7 days</p>
                    <div style="overflow: auto; max-height: 450px;">
                        <table class="sub-table">
                            <thead><tr><th>Session Title</th><th>Date</th><th>Time</th></tr></thead>
                            <tbody>
                                <?php
                                $nextweek=date("Y-m-d",strtotime("+1 week"));
                                $sql_s= "select schedule.title,schedule.scheduledate,schedule.scheduletime from schedule where schedule.docid='$userid' and schedule.scheduledate>='$today' and schedule.scheduledate<='$nextweek' and schedule.is_cancelled=0 order by schedule.scheduledate desc"; 
                                $res_s= $database->query($sql_s);
                                if($res_s->num_rows==0){
                                    echo '<tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b;">No sessions found.</td></tr>';
                                } else {
                                    while($row=$res_s->fetch_assoc()){
                                        echo '<tr>
                                            <td><span style="font-weight:600; color:#1e1b4b;">'.substr($row["title"],0,30).'</span></td>
                                            <td>'.$row["scheduledate"].'</td>
                                            <td><span style="color:var(--primary); font-weight:600;">'.substr($row["scheduletime"],0,5).'</span></td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");
date_default_timezone_set('Asia/kathmandu');
$today = date('Y-m-d');

$patientrow = $database->query("select * from patient;");
$doctorrow = $database->query("select * from doctor;");
$appointmentrow = $database->query("select appointment.appoid from appointment inner join schedule on appointment.scheduleid=schedule.scheduleid where schedule.scheduledate>='$today' and schedule.is_cancelled=0;");
$schedulerow = $database->query("select * from schedule where scheduledate='$today' and is_cancelled=0;");
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
        
        /* Modern Header Search */
        .nav-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
        .header-search { flex: 1; display: flex; gap: 10px; }
        .header-searchbar { 
            flex: 1; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; 
            background: #fff; font-size: 14px; transition: all 0.3s;
        }
        .header-searchbar:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); outline: none; }
        
        .status-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-mini-card {
            background: #fff;
            padding: 25px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center; gap: 18px; border: 1px solid #f1f5f9; transition: transform 0.3s;
        }
        .status-mini-card:hover { transform: translateY(-5px); }
        .status-icon {
            width: 55px; height: 55px; background: var(--primary-soft); border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--primary);
        }
        .status-info h4 { margin: 0; font-size: 22px; color: #1e1b4b; font-weight: 800; }
        .status-info p { margin: 2px 0 0 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        .cute-card {
            background: #fff; border-radius: 24px; padding: 25px; box-shadow: var(--card-shadow); border: 1px solid #f1f5f9; height: 100%;
        }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { font-size: 19px; color: #1e1b4b; font-weight: 800; margin: 0; }
        .view-all { color: var(--primary); font-weight: 700; font-size: 14px; text-decoration: none; }

        .sub-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        .sub-table thead th { padding: 12px; color: #94a3b8; font-size: 12px; font-weight: 700; text-transform: uppercase; text-align: left; }
        .sub-table tbody tr { background: #f8fafc; border-radius: 12px; transition: all 0.2s; }
        .sub-table tbody tr:hover { background: #f1f5f9; }
        .sub-table td { padding: 15px 12px; font-size: 14px; color: #334155; font-weight: 500; }
        .sub-table td:first-child { border-radius: 12px 0 0 12px; padding-left: 20px; }
        .sub-table td:last-child { border-radius: 0 12px 12px 0; padding-right: 20px; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px; }

        .quick-actions-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
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
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@edoc.com</p>
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
                <tr class="menu-row"><td class="menu-btn menu-icon-doctor"><a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-schedule"><a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-appoinment"><a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-appoinment"><a href="reviews.php" class="non-style-link-menu"><div><p class="menu-text">Reviews</p></div></a></td></tr>
                <tr class="menu-row"><td class="menu-btn menu-icon-patient"><a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></div></a></td></tr>
            </table>
        </div>
        <div class="dash-body">
            <div class="nav-bar">
                <form action="doctors.php" method="post" class="header-search">
                    <input type="search" name="search" class="header-searchbar" placeholder="Search Doctor name or Email" list="doctors">
                    <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("select docname,docemail from doctor;");
                        for ($y=0;$y<$list11->num_rows;$y++){
                            $row00=$list11->fetch_assoc();
                            $d=$row00["docname"];
                            $c=$row00["docemail"];
                            echo "<option value='$d'>";
                            echo "<option value='$c'>";
                        };
                        echo '</datalist>';
                    ?>
                    <input type="Submit" value="Search" class="login-btn btn-primary-soft btn" style="padding: 10px 25px; border-radius: 12px;">
                </form>
                <div style="text-align: right;">
                    <p style="margin:0; color:#64748b; font-size:12px; font-weight:600; text-transform: uppercase;">Today's Date</p>
                    <h3 style="margin:0; color:var(--primary); font-weight:800; font-size: 16px;"><?php echo $today; ?></h3>
                </div>
            </div>

            <div class="status-row">
                <div class="status-mini-card">
                    <div class="status-icon">👨‍⚕️</div>
                    <div class="status-info"><h4><?php echo $doctorrow->num_rows ?></h4><p>Doctors</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">👥</div>
                    <div class="status-info"><h4><?php echo $patientrow->num_rows ?></h4><p>Patients</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">📅</div>
                    <div class="status-info"><h4><?php echo $appointmentrow->num_rows ?></h4><p>New Bookings</p></div>
                </div>
                <div class="status-mini-card">
                    <div class="status-icon">⏱️</div>
                    <div class="status-info"><h4><?php echo $schedulerow->num_rows ?></h4><p>Today Sessions</p></div>
                </div>
            </div>

            <div class="quick-actions-row">
                <a href="doctors.php?action=add" class="cute-card" style="text-decoration: none; border: 2px solid var(--primary-soft); display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 30px; background: var(--primary-soft); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">👨‍⚕️</div>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #1e1b4b;">Add New Doctor</h3>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">Register a new medical professional</p>
                    </div>
                </a>
                <a href="schedule.php?action=add-session" class="cute-card" style="text-decoration: none; border: 2px solid var(--primary-soft); display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 30px; background: var(--primary-soft); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">📅</div>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #1e1b4b;">Create Schedule</h3>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">Plan new doctor sessions</p>
                    </div>
                </a>
            </div>

            <div class="dashboard-grid">
                <div class="cute-card">
                    <div class="section-header">
                        <h2>Upcoming Appointments</h2>
                        <a href="appointment.php" class="view-all">View All →</a>
                    </div>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Next 7 days overview</p>
                    <div style="overflow: auto; max-height: 400px;">
                        <table class="sub-table">
                            <thead><tr><th>No.</th><th>Patient</th><th>Doctor</th><th>Session</th></tr></thead>
                            <tbody>
                                <?php
                                $nextweek=date("Y-m-d",strtotime("+1 week"));
                                $sql_app = "select appointment.apponum,schedule.title,doctor.docname,patient.pname,schedule.scheduledate,schedule.scheduletime from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join patient on patient.pid=appointment.pid inner join doctor on schedule.docid=doctor.docid where schedule.scheduledate>='$today' and schedule.scheduledate<='$nextweek' and schedule.is_cancelled=0 order by schedule.scheduledate desc";
                                $res_app = $database->query($sql_app);
                                if($res_app->num_rows==0){
                                    echo '<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b;">No appointments found for next week.</td></tr>';
                                } else {
                                    while($row=$res_app->fetch_assoc()){
                                        echo '<tr>
                                            <td><span style="font-weight:700; color:var(--primary);">'.$row["apponum"].'</span></td>
                                            <td><span style="font-weight:600; color:#1e1b4b;">'.substr($row["pname"],0,20).'</span></td>
                                            <td>'.substr($row["docname"],0,20).'</td>
                                            <td>'.substr($row["title"],0,20).'</td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cute-card">
                    <div class="section-header">
                        <h2>Upcoming Sessions</h2>
                        <a href="schedule.php" class="view-all">View All →</a>
                    </div>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Sessions scheduled for next 7 days</p>
                    <div style="overflow: auto; max-height: 400px;">
                        <table class="sub-table">
                            <thead><tr><th>Title</th><th>Doctor</th><th>Date & Time</th></tr></thead>
                            <tbody>
                                <?php
                                $sql_sch = "select schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime from schedule inner join doctor on schedule.docid=doctor.docid where schedule.scheduledate>='$today' and schedule.scheduledate<='$nextweek' and schedule.is_cancelled=0 order by schedule.scheduledate desc";
                                $res_sch = $database->query($sql_sch);
                                if($res_sch->num_rows==0){
                                    echo '<tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b;">No sessions scheduled for next week.</td></tr>';
                                } else {
                                    while($row=$res_sch->fetch_assoc()){
                                        echo '<tr>
                                            <td><span style="font-weight:600; color:var(--primary);">'.substr($row["title"],0,30).'</span></td>
                                            <td>'.substr($row["docname"],0,20).'</td>
                                            <td>'.substr($row["scheduledate"],0,10).'<br><span style="font-size:12px; opacity:0.7;">'.substr($row["scheduletime"],0,5).'</span></td>
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
<?php
// ───────────────────────────────────────────────────────────────────
// EARLY: handle redirects BEFORE any HTML is sent to the browser.
// When coming from schedule.php with ?id=X (no step param), redirect
// cleanly to step=3 so the Verify page loads correctly.
// ───────────────────────────────────────────────────────────────────
if (isset($_GET['id']) && !isset($_GET['step'])) {
    $redirect_id = (int)$_GET['id'];
    header("Location: booking.php?step=2&id={$redirect_id}");
    exit;
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
    <link rel="stylesheet" href="../css/booking.css">
        
    <title>Booking Wizard</title>
</head>
<body>
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

    date_default_timezone_set('Asia/kathmandu');
    $today = date('Y-m-d');

    $step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

    // Helper to get step class
    function getStepClass($currentStep, $targetStep) {
        if ($currentStep == $targetStep) return 'active';
        if ($currentStep > $targetStep) return 'completed';
        return '';
    }
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
                    <td class="menu-btn menu-icon-session menu-active menu-icon-session-active">
                        <a href="schedule.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Scheduled Sessions</p></div></a>
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

        <div class="dash-body">
            <div class="booking-container">
                <!-- Progress Bar -->
                <div class="progress-wrapper">
                    <div class="progress-line"></div>
                    <div class="progress-line-active" style="width: <?php echo (($step-1)/5)*100; ?>%"></div>
                    
                    <div class="step-item <?php echo getStepClass($step, 1); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 1</div>
                        <div class="step-subtext">Select Department</div>
                    </div>
                    <div class="step-item <?php echo getStepClass($step, 2); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 2</div>
                        <div class="step-subtext">Select the doctor</div>
                    </div>
                    <div class="step-item <?php echo getStepClass($step, 3); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 3</div>
                        <div class="step-subtext">Appointment time</div>
                    </div>
                    <div class="step-item <?php echo getStepClass($step, 4); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 4</div>
                        <div class="step-subtext">Verify Patient</div>
                    </div>
                    <div class="step-item <?php echo getStepClass($step, 5); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 5</div>
                        <div class="step-subtext">Payments</div>
                    </div>
                    <div class="step-item <?php echo getStepClass($step, 6); ?>">
                        <div class="step-circle"></div>
                        <div class="step-label">Step 6</div>
                        <div class="step-subtext">Appointment</div>
                    </div>
                </div>

                <div class="step-page">
                    <?php
                    // ── Display any error returned from booking-complete.php ─
                    $booking_err = isset($_GET['err']) ? htmlspecialchars(urldecode($_GET['err'])) : '';
                    if ($booking_err):
                    ?>
                    <div style="background:#fee2e2; border-left:4px solid #ef4444; color:#991b1b;
                                padding:14px 18px; border-radius:8px; margin-bottom:20px;
                                font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo $booking_err; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['reschedule_id'])): 
                        $res_id = (int)$_SESSION['reschedule_id'];
                        $orig_q = "SELECT s.scheduledate, s.scheduletime, a.apponum, s.title, t.slot_time 
                                   FROM appointment a 
                                   INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
                                   LEFT JOIN timeslots t ON a.slot_id = t.slot_id
                                   WHERE a.appoid = ?";
                        $orig_stmt = $database->prepare($orig_q);
                        $orig_stmt->bind_param("i", $res_id);
                        $orig_stmt->execute();
                        $orig = $orig_stmt->get_result()->fetch_assoc();
                        
                        $display_time = !empty($orig['slot_time']) ? date("h:i A", strtotime($orig['slot_time'])) : date("h:i A", strtotime($orig['scheduletime']));
                    ?>
                    <div style="background:#fff7ed; border-left:4px solid #f97316; color:#9a3412;
                                padding:18px; border-radius:12px; margin-bottom:25px;
                                font-size:14px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                            <div style="background:#f97316; color:#fff; padding:6px; border-radius:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            </div>
                            <b style="font-size:16px;">Rescheduling Mode</b>
                        </div>
                        <div style="background:#fff; border:1px solid #fed7aa; padding:12px; border-radius:8px; display:flex; flex-wrap:wrap; gap:15px;">
                            <div>
                                <span style="color:#7c2d12; font-size:11px; text-transform:uppercase; font-weight:700; display:block;">Current Booking</span>
                                <span style="font-size:15px; font-weight:600;"><?php echo $orig['title']; ?></span>
                            </div>
                            <div style="border-left:1px solid #fed7aa; padding-left:15px;">
                                <span style="color:#7c2d12; font-size:11px; text-transform:uppercase; font-weight:700; display:block;">Date & Time</span>
                                <span style="font-size:14px;"><?php echo $orig['scheduledate']; ?> at <?php echo $display_time; ?></span>
                            </div>
                            <div style="border-left:1px solid #fed7aa; padding-left:15px;">
                                <span style="color:#7c2d12; font-size:11px; text-transform:uppercase; font-weight:700; display:block;">Slot No.</span>
                                <span style="font-size:14px;"><?php echo $orig['apponum']; ?></span>
                            </div>
                        </div>
                        <p style="margin-top:12px; font-size:13px; opacity:0.8;">Select a new slot below to move your booking. No extra payment required.</p>
                    </div>
                    <?php endif; ?>

                    <?php
                    switch($step) {
                        case 1: // Select Department
                            ?>
                            <div class="booking-card">
                                <h2 style="margin-bottom:20px;">Find By Speciality</h2>
                                <div class="filter-row">
                                    <form action="booking.php" method="GET" style="display:flex; width:100%; gap:10px;">
                                        <input type="hidden" name="step" value="2">
                                        <select name="spec" class="input-field" style="flex:2;" required>
                                            <option value="" disabled selected>Select Speciality</option>
                                            <?php
                                            $list11 = $database->query("select * from specialties order by sname asc;");
                                            for ($y=0;$y<$list11->num_rows;$y++){
                                                $row00=$list11->fetch_assoc();
                                                $sn=$row00["sname"];
                                                $sid=$row00["id"];
                                                echo "<option value='$sid'>$sn</option>";
                                            }
                                            ?>
                                        </select>
                                        <button type="submit" class="proceed-btn">Next Step &rarr;</button>
                                    </form>
                                </div>
                            </div>
                            <?php
                            break;

                        case 2: // Select Doctor
                            $specid = isset($_GET['spec']) ? (int)$_GET['spec'] : 0;
                            $spec_name = "All Specialities";

                            if ($specid > 0) {
                                $spec_r = $database->prepare("SELECT sname FROM specialties WHERE id=?");
                                $spec_r->bind_param('i', $specid);
                                $spec_r->execute();
                                $res_spec = $spec_r->get_result()->fetch_assoc();
                                if ($res_spec) $spec_name = $res_spec['sname'];
                            }

                            $req_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                            if ($req_id > 0 && $specid === 0) {
                                // If we have a direct session ID but no spec, try to find the spec for the header
                                $spec_q = $database->prepare("SELECT s.sname, s.id FROM specialties s JOIN doctor d ON d.specialties=s.id JOIN schedule sch ON sch.docid=d.docid WHERE sch.scheduleid=?");
                                $spec_q->bind_param('i', $req_id);
                                $spec_q->execute();
                                $res_sq = $spec_q->get_result()->fetch_assoc();
                                if ($res_sq) {
                                    $spec_name = $res_sq['sname'];
                                    $specid = $res_sq['id'];
                                }
                            }

                            ?>
                            <style>
                                /* Slot picker styles (Step 2 inline) */
                                .slot-picker-panel    { display:none; padding:16px; background:#f8faff; border-top:1px solid #e5eaf5; }
                                .slot-picker-panel.open { display:block; animation: fadeIn .3s ease; }
                                .slot-grid            { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
                                .slot-chip            { padding:8px 16px; border-radius:20px; border:2px solid #0a76d8;
                                                        cursor:pointer; font-size:13px; font-weight:600; color:#0a76d8;
                                                        background:#fff; transition:all .2s; }
                                .slot-chip:hover,
                                .slot-chip.selected   { background:#0a76d8; color:#fff; }
                                .slot-chip.full       { border-color:#ccc; color:#aaa; cursor:not-allowed; }
                                .slot-loading         { color:#888; font-size:13px; padding:10px 0; }
                                .slot-proceed-wrap    { margin-top:16px; display:none; }
                                .session-row-doc      { display:flex; justify-content:space-between; align-items:center;
                                                        padding:10px 0; border-bottom:1px solid #f0f0f0; cursor:pointer; }
                                .session-row-doc:hover .session-pick-btn { opacity:1; }
                                .session-pick-btn     { background:#0a76d8; color:#fff; border:none; padding:6px 14px;
                                                        border-radius:6px; font-size:12px; cursor:pointer; opacity:.8; transition:opacity .2s; }
                                .tokens-badge         { font-size:12px; padding:3px 10px; border-radius:20px; }
                                .tokens-ok            { background:#dcfce7; color:#166534; }
                                .tokens-low           { background:#fef9c3; color:#854d0e; }
                                .tokens-full          { background:#fee2e2; color:#991b1b; }
                            </style>

                            <div class="booking-card">
                                <div class="filter-row">
                                    <div>
                                        <h2 style="margin:0;">Doctors &mdash; <?php echo htmlspecialchars($spec_name); ?></h2>
                                    </div>
                                    <div class="search-group">
                                        <form action="booking.php" method="GET" style="display:flex;">
                                            <input type="hidden" name="step" value="2">
                                            <input type="hidden" name="spec" value="<?php echo $specid; ?>">
                                            <input type="text" name="doc_search" class="input-field" placeholder="Find Doctor" style="border-radius:6px 0 0 6px;">
                                            <button type="submit" class="search-btn">
                                                <img src="../img/search-white.svg" width="15">
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <?php
                                $doc_q = "SELECT doctor.*, specialties.sname FROM doctor INNER JOIN specialties ON doctor.specialties=specialties.id WHERE 1=1";
                                $doc_params = [];
                                $doc_types  = '';

                                if ($specid > 0) {
                                    $doc_q .= " AND specialties=?";
                                    $doc_params[] = $specid;
                                    $doc_types .= 'i';
                                } elseif ($req_id > 0) {
                                    // If only session ID is known, find its doctor
                                    $doc_q .= " AND doctor.docid = (SELECT docid FROM schedule WHERE scheduleid = ?)";
                                    $doc_params[] = $req_id;
                                    $doc_types .= 'i';
                                }

                                if (!empty($_GET['doc_search'])) {
                                    $search  = '%' . $_GET['doc_search'] . '%';
                                    $doc_q  .= " AND (docname LIKE ? OR docemail LIKE ?)";
                                    $doc_params[] = $search;
                                    $doc_params[] = $search;
                                    $doc_types   .= 'ss';
                                }

                                $doc_stmt = $database->prepare($doc_q);
                                if (!empty($doc_types)) {
                                    $doc_stmt->bind_param($doc_types, ...$doc_params);
                                }
                                $doc_stmt->execute();
                                $docs = $doc_stmt->get_result();

                                if ($docs->num_rows === 0) {
                                    echo '<p style="color:#888;">No doctors found for this speciality.</p>';
                                } else {
                                    while ($doc = $docs->fetch_assoc()) {
                                        $docid = (int)$doc['docid'];

                                        // Fetch upcoming sessions for this doctor with real-time token counts
                                        $sess_stmt = $database->prepare("
                                            SELECT
                                                s.scheduleid,
                                                s.title,
                                                s.scheduledate,
                                                s.scheduletime,
                                                s.nop                       AS token_limit,
                                                COUNT(a.appoid)             AS booked_count,
                                                (s.nop - COUNT(a.appoid))   AS tokens_left
                                            FROM schedule s
                                            LEFT JOIN appointment a ON a.scheduleid = s.scheduleid
                                            WHERE s.docid = ?
                                              AND s.scheduledate >= ?
                                            GROUP BY s.scheduleid
                                            ORDER BY s.scheduledate ASC
                                            LIMIT 15
                                        ");
                                        $sess_stmt->bind_param('is', $docid, $today);
                                        $sess_stmt->execute();
                                        $sessions = $sess_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                        ?>

                                        <div class="doctor-card" style="flex-direction:column;">
                                            <!-- Doctor header row -->
                                            <div style="display:flex; align-items:center; gap:16px; padding:20px;">
                                                <img src="../img/user.png" class="doc-img">
                                                <div>
                                                    <h3 style="margin:0 0 4px;"><?php echo htmlspecialchars($doc['docname']); ?></h3>
                                                    <p style="color:#666; font-size:13px; margin:0;"><?php echo htmlspecialchars($doc['sname']); ?></p>
                                                    <p style="color:#888; font-size:12px; margin-top:4px;">
                                                        🕐 <?php echo $doc['doc_slot_duration']; ?> min slots
                                                        &nbsp;|&nbsp;
                                                        🎫 Up to <?php echo $doc['doc_max_tokens']; ?> patients/session
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Sessions list -->
                                            <?php if (empty($sessions)): ?>
                                                <p style="padding:0 20px 20px; color:#aaa; font-size:13px;">No upcoming sessions.</p>
                                            <?php else: ?>
                                                <div style="padding:0 20px 16px;">
                                                    <p style="font-size:13px; font-weight:700; color:#444; margin-bottom:8px;">Select a session to view available slots:</p>
                                                    <?php foreach ($sessions as $sess):
                                                        $sid    = $sess['scheduleid'];
                                                        $left   = max(0, (int)$sess['tokens_left']);
                                                        $is_full = ($left <= 0);
                                                        $badge_class = $is_full ? 'tokens-full' : ($left <= 3 ? 'tokens-low' : 'tokens-ok');
                                                    ?>
                                                        <div class="session-row-doc <?php echo $is_full ? 'disabled' : ''; ?>">
                                                            <div>
                                                                <div style="font-weight:600; font-size:14px;">
                                                                    <?php echo htmlspecialchars($sess['title']); ?>
                                                                </div>
                                                                <div style="font-size:12px; color:#777; margin-top:2px;">
                                                                    📅 <?php echo $sess['scheduledate']; ?>
                                                                    (<?php echo date('l', strtotime($sess['scheduledate'])); ?>)
                                                                    &nbsp;⏰ <?php echo date('h:i A', strtotime($sess['scheduletime'])); ?>
                                                                </div>
                                                            </div>
                                                            <div style="display:flex; align-items:center; gap:10px;">
                                                                <span class="tokens-badge <?php echo $badge_class; ?>">
                                                                    <?php echo $is_full ? 'Full' : "$left left"; ?>
                                                                </span>
                                                                <?php if (!$is_full): ?>
                                                                    <button class="session-pick-btn"
                                                                            onclick="loadSlots(<?php echo $sid; ?>, this)"
                                                                            data-sid="<?php echo $sid; ?>">
                                                                        View Slots
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span style="font-size:12px; color:#aaa;">Fully Booked</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Inline slot picker (hidden until button clicked) -->
                                                        <div class="slot-picker-panel" id="slot-panel-<?php echo $sid; ?>">
                                                            <p style="font-size:13px; font-weight:700; color:#0a76d8; margin-bottom:4px;">Available time slots:</p>
                                                            <div class="slot-loading" id="loading-<?php echo $sid; ?>">Loading slots...</div>
                                                            <div class="slot-grid"  id="slots-<?php echo $sid; ?>"></div>
                                                            <!-- Hidden form to carry slot through to Step 3 -->
                                                            <div class="slot-proceed-wrap" id="proceed-<?php echo $sid; ?>">
                                                                <form method="GET" action="booking.php">
                                                                    <input type="hidden" name="step"      value="3">
                                                                    <input type="hidden" name="id"        value="<?php echo $sid; ?>">
                                                                    <input type="hidden" name="slot_id"   id="selected-slot-<?php echo $sid; ?>" value="0">
                                                                    <input type="hidden" name="slot_num"  id="selected-num-<?php echo $sid; ?>"  value="">
                                                                    <input type="hidden" name="spec"      value="<?php echo $specid; ?>">
                                                                    <button type="submit" class="proceed-btn" style="margin-top:10px;">
                                                                        Confirm Slot &amp; Continue &rarr;
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                    } // end while doctors
                                } // end else
                                ?>

                                <div style="margin-top:20px;">
                                    <a href="booking.php?step=1" class="proceed-btn" style="background:#666;">&larr; Prev</a>
                                </div>
                            </div>

                            <script>
                            /**
                             * loadSlots(scheduleid, buttonEl)
                             * Calls get-slots.php via AJAX and renders chip buttons for each available slot.
                             */
                            var activePanel = null;

                            function loadSlots(sid, btn) {
                                // Close any previously open panel
                                if (activePanel && activePanel !== sid) {
                                    var prev = document.getElementById('slot-panel-' + activePanel);
                                    if (prev) prev.classList.remove('open');
                                }
                                activePanel = sid;

                                var panel    = document.getElementById('slot-panel-' + sid);
                                var loading  = document.getElementById('loading-'     + sid);
                                var grid     = document.getElementById('slots-'       + sid);
                                var proceed  = document.getElementById('proceed-'     + sid);

                                panel.classList.toggle('open');

                                // Only fetch once; skip if already loaded
                                if (grid.dataset.loaded === '1') return;

                                loading.style.display = 'block';
                                grid.innerHTML        = '';
                                proceed.style.display = 'none';

                                fetch('get-slots.php?scheduleid=' + sid)
                                    .then(function(res) { return res.json(); })
                                    .then(function(data) {
                                        loading.style.display = 'none';

                                        if (data.error) {
                                            grid.innerHTML = '<p style="color:red;font-size:13px;">' + data.error + '</p>';
                                            return;
                                        }

                                        if (data.fully_booked || data.slots.length === 0) {
                                            grid.innerHTML = '<p style="color:#aaa;font-size:13px;">No available slots for this session.</p>';
                                            return;
                                        }

                                        data.slots.forEach(function(slot) {
                                            var chip = document.createElement('div');
                                            chip.className    = 'slot-chip';
                                            if (slot.is_booked == 1) {
                                                chip.classList.add('full');
                                                chip.title = 'This slot is already booked';
                                            }
                                            chip.textContent  = '#' + slot.slot_number + ' — ' + slot.slot_time_display;
                                            
                                            if (slot.is_booked == 0) {
                                                chip.onclick = function() {
                                                    // Deselect all chips
                                                    grid.querySelectorAll('.slot-chip').forEach(function(c){ c.classList.remove('selected'); });
                                                    chip.classList.add('selected');

                                                    var realSlotId  = (slot.slot_id !== null && slot.slot_id !== undefined) ? slot.slot_id : 0;
                                                    var realSlotNum = slot.slot_number;

                                                    document.getElementById('selected-slot-' + sid).value = realSlotId;
                                                    document.getElementById('selected-num-'  + sid).value = realSlotNum;
                                                    proceed.style.display = 'block';
                                                };
                                            }
                                            grid.appendChild(chip);
                                        });

                                        grid.dataset.loaded = '1';
                                    })
                                    .catch(function(err) {
                                        loading.style.display = 'none';
                                        grid.innerHTML = '<p style="color:red;font-size:13px;">Failed to load slots. Please try again.</p>';
                                    });
                            }

                            // Auto-open slot picker if an ID is provided
                            <?php if ($req_id > 0): ?>
                            window.addEventListener('load', function() {
                                var btn = document.querySelector('button[data-sid="<?php echo $req_id; ?>"]');
                                if (btn) loadSlots(<?php echo $req_id; ?>, btn);
                            });
                            <?php endif; ?>
                            </script>

                            <?php
                            break;

                        case 3: // Slot confirmed — Verify Patient details
                            $sid     = (int)$_GET['id'];
                            $slot_id  = isset($_GET['slot_id'])  ? (int)$_GET['slot_id']  : 0;
                            $slot_num = isset($_GET['slot_num'])  ? (int)$_GET['slot_num']  : 0;  // chosen token number
                            $specid   = isset($_GET['spec'])      ? (int)$_GET['spec']      : 0;

                            // Fetch session + doctor info
                            $vstmt = $database->prepare("
                                SELECT s.*, d.docname, d.doc_slot_duration, d.specialties AS doc_spec
                                FROM schedule s
                                JOIN doctor d ON s.docid = d.docid
                                WHERE s.scheduleid = ?
                            ");
                            $vstmt->bind_param('i', $sid);
                            $vstmt->execute();
                            $row = $vstmt->get_result()->fetch_assoc();

                            if (!$row) {
                                echo '<p style="color:red;">Session not found. <a href="booking.php?step=1">Start over</a></p>';
                                break;
                            }

                            if ($specid === 0 && isset($row['doc_spec'])) {
                                $specid = (int)$row['doc_spec'];
                            }


                            $docname       = $row['docname'];
                            $scheduledate  = $row['scheduledate'];
                            $scheduletime  = $row['scheduletime'];
                            $slot_duration = (int)$row['doc_slot_duration'];

                            // ── Resolve the chosen time slot ────────────────────────────────────────
                            // slot_id > 0  → real row in timeslots table (pre-generated by admin)
                            // slot_id == 0 → calculated fallback; use slot_num from AJAX selection
                            // slot_num == 0 → nothing selected yet, auto-calculate next token

                            if ($slot_id > 0) {
                                // Real named slot: look up the timeslots table
                                $slotrstmt = $database->prepare("
                                    SELECT slot_number, slot_time, is_booked
                                    FROM timeslots
                                    WHERE slot_id = ? AND scheduleid = ?
                                ");
                                $slotrstmt->bind_param('ii', $slot_id, $sid);
                                $slotrstmt->execute();
                                $slot_row = $slotrstmt->get_result()->fetch_assoc();

                                if (!$slot_row) {
                                    // slot_id doesn't exist in DB — fall through to calculated
                                    $slot_id = 0;
                                } elseif ((int)$slot_row['is_booked'] === 1) {
                                    echo '<div style="padding:20px; color:#dc3545;">&#9888;&#65039; This slot was just taken. <a href="booking.php?step=2&spec=' . $specid . '">Choose another.</a></div>';
                                    break;
                                } else {
                                    $apponum          = (int)$slot_row['slot_number'];
                                    $appointment_time = date('h:i A', strtotime($slot_row['slot_time']));
                                }
                            }

                            if ($slot_id === 0) {
                                // Calculated path: use the slot_num the patient selected,
                                // or fall back to next available token if nothing was passed.

                                if ($slot_num > 0) {
                                    $apponum = $slot_num;
                                } else {
                                    // Absolute fallback — Find the smallest available token number (the "First Gap")
                                    $bt_stmt = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ? ORDER BY apponum ASC");
                                    $bt_stmt->bind_param('i', $sid);
                                    $bt_stmt->execute();
                                    $bt_res = $bt_stmt->get_result();
                                    $booked_tokens = [];
                                    while ($bt_row = $bt_res->fetch_assoc()) {
                                        $booked_tokens[] = (int)$bt_row['apponum'];
                                    }
                                    $bt_stmt->close();

                                    $apponum = 1;
                                    while (in_array($apponum, $booked_tokens)) {
                                        $apponum++;
                                    }
                                }

                                $offset_mins      = ($apponum - 1) * $slot_duration;
                                $appointment_time = date('h:i A', strtotime("+$offset_mins minutes", strtotime($scheduletime)));
                            }
                            ?>
                            <div class="booking-card">
                                <h2>Verify Your Booking</h2><br>
                                <div style="display:flex; gap:24px; flex-wrap:wrap;">

                                    <!-- Session Details -->
                                    <div style="flex:1; min-width:220px; border:1px solid #e5eaf5; padding:20px; border-radius:10px; background:#f8faff;">
                                        <h3 style="color:var(--primary-color); margin-top:0;">Session Details</h3>
                                        <hr style="border-color:#e5eaf5;"><br>
                                        <p><b>Doctor:</b> <?php echo htmlspecialchars($docname); ?></p>
                                        <p><b>Session:</b> <?php echo htmlspecialchars($row['title']); ?></p>
                                        <p><b>Date:</b> <?php echo $scheduledate; ?> (<?php echo date('l', strtotime($scheduledate)); ?>)</p>
                                        <p><b>Session Starts:</b> <?php echo date('h:i A', strtotime($scheduletime)); ?></p>
                                        <div style="background:#e0f2fe; border-radius:8px; padding:12px 16px; margin-top:10px;">
                                            <div style="font-size:12px; color:#0369a1; font-weight:700;">YOUR TIME SLOT</div>
                                            <div style="font-size:26px; font-weight:700; color:#0a76d8;"><?php echo $appointment_time; ?></div>
                                            <div style="font-size:12px; color:#555;">Token #<?php echo $apponum; ?></div>
                                        </div>
                                        <p style="margin-top:12px;"><b>Fee:</b> <b style="color:var(--primary-color)">NRP. 700.00</b></p>
                                    </div>

                                    <!-- Patient Details -->
                                    <div style="flex:1; min-width:220px; border:1px solid #e5eaf5; padding:20px; border-radius:10px;">
                                        <h3 style="color:var(--primary-color); margin-top:0;">Your Details</h3>
                                        <hr style="border-color:#e5eaf5;"><br>
                                        <p><b>Name:</b> <?php echo htmlspecialchars($username); ?></p>
                                        <p><b>Email:</b> <?php echo htmlspecialchars($useremail); ?></p>
                                        <p><b>Phone:</b> <?php echo htmlspecialchars($userfetch['ptel']); ?></p>
                                    </div>
                                </div>

                                <div style="margin-top:24px; display:flex; justify-content:space-between;">
                                    <a href="booking.php?step=2&id=<?php echo $sid; ?>&spec=<?php echo $specid; ?>" class="proceed-btn" style="background:#666;">&larr; Change Slot</a>
                                    <a href="booking.php?step=5&id=<?php echo $sid; ?>&slot_id=<?php echo $slot_id; ?>&slot_num=<?php echo $apponum; ?>&apponum=<?php echo $apponum; ?>&date=<?php echo $scheduledate; ?>&spec=<?php echo $specid; ?>" class="proceed-btn">Confirm &amp; Pay &rarr;</a>
                                </div>
                            </div>
                            <?php
                            break;

                        case 4: // Legacy: schedule.php links without a step param — now handled
                                // by the early redirect at the top. This case is a safety fallback.
                            $sid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                            if ($sid > 0) {
                                echo '<script>window.location.replace("booking.php?step=3&id=' . $sid . '&slot_id=0&slot_num=0");</script>';
                            } else {
                                echo '<div style="padding:30px; color:#dc3545;">Invalid session. <a href="booking.php?step=1">Start over</a></div>';
                            }

                        case 5: // Payments
                            $sid     = (int)$_GET['id'];
                            $slot_id = (int)($_GET['slot_id']  ?? 0);
                            $slot_num = (int)($_GET['slot_num'] ?? 0);
                            $apponum = (int)($_GET['apponum']  ?? 0);
                            $specid  = (int)($_GET['spec']     ?? 0);
                            $date    = $_GET['date'] ?? ''; // Fetch date from URL
                            ?>
                            <div class="booking-card" style="max-width:500px; margin:0 auto;">
                                <h2 style="text-align:center;"><?php echo isset($_SESSION['reschedule_id']) ? 'Confirm Reschedule' : 'Secure Payment'; ?></h2>
                                <p style="text-align:center; color:#666;">
                                    <?php echo isset($_SESSION['reschedule_id']) 
                                        ? 'Your previous payment of <b>NRP. 700.00</b> will be applied to this new slot.' 
                                        : 'Amount to Pay: <b>NRP. 700.00</b>'; ?>
                                </p>
                                <br>
                                <form action="booking-complete.php" method="POST">
                                    <input type="hidden" name="scheduleid" value="<?php echo $sid; ?>">
                                    <input type="hidden" name="slot_id"    value="<?php echo $slot_id; ?>">
                                    <input type="hidden" name="slot_num"   value="<?php echo $slot_num; ?>">
                                    <input type="hidden" name="apponum"    value="<?php echo $apponum; ?>">
                                    <input type="hidden" name="date"       value="<?php echo $date; ?>">

                                    <?php if (!isset($_SESSION['reschedule_id'])): ?>
                                    <div style="background:#f3e8ff; border:1px solid #c084fc; padding:20px; border-radius:10px; text-align:center; margin-bottom:20px;">
                                        <h3 style="color:#5C2D91; margin-top:0; font-size:22px; margin-bottom:5px;">Khalti Digital Wallet</h3>
                                        <p style="color:#7e22ce; font-size:13px; margin-bottom:15px;">Pay securely via Khalti</p>
                                        
                                        <div class="field" style="text-align:left; margin-bottom:15px;">
                                            <label style="color:#5C2D91; font-weight:600;">Khalti Mobile Number</label><br>
                                            <input type="tel" class="input-field" style="width:100%; border-color:#d8b4fe;" placeholder="98XXXXXXXX" pattern="(98|97)[0-9]{8}" required>
                                        </div>
                                        <div class="field" style="text-align:left; margin-bottom:10px;">
                                            <label style="color:#5C2D91; font-weight:600;">Khalti PIN</label><br>
                                            <input type="password" class="input-field" style="width:100%; border-color:#d8b4fe;" placeholder="****" maxlength="4" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="booknow" class="proceed-btn" style="width:100%; justify-content:center; padding:15px; background:#5C2D91; color:#fff; font-size:16px;">Pay NRP. 700.00 with Khalti</button>
                                    <?php else: ?>
                                    <div style="background:#f0f9ff; border:1px dashed #0ea5e9; padding:20px; border-radius:10px; text-align:center; margin-bottom:20px;">
                                        <p style="color:#0369a1; font-size:14px; margin:0;">Balance Paid: <b>NRP. 700.00</b></p>
                                        <p style="color:#64748b; font-size:12px; margin-top:5px;">Click the button below to move your booking to the new time slot.</p>
                                    </div>
                                    <button type="submit" name="booknow" class="proceed-btn" style="width:100%; justify-content:center; padding:15px; background:#0ea5e9;">Confirm Reschedule &rarr;</button>
                                    <?php endif; ?>
                                </form>
                                <div style="text-align:center; margin-top:15px;">
                                    <a href="booking.php?step=3&id=<?php echo $sid; ?>&slot_id=<?php echo $slot_id; ?>&spec=<?php echo $specid; ?>" style="color:#666; text-decoration:none; font-size:13px;">Cancel and go back</a>
                                </div>
                            </div>
                            <?php
                            break;

                        case 6: // Final Step - Confirmation
                            $sid      = (int)$_GET['id'];
                            $slot_id  = (int)($_GET['slot_id']  ?? 0);
                            $slot_num = (int)($_GET['slot_num'] ?? 0);
                            $apponum  = (int)($_GET['apponum']  ?? 0);

                             // If apponum was not passed, calculate it using Gap Analysis
                             if ($apponum === 0) {
                                $bt_stmt = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ? ORDER BY apponum ASC");
                                $bt_stmt->bind_param('i', $sid);
                                $bt_stmt->execute();
                                $bt_res = $bt_stmt->get_result();
                                $booked_tokens = [];
                                while ($bt_row = $bt_res->fetch_assoc()) {
                                    $booked_tokens[] = (int)$bt_row['apponum'];
                                }
                                $bt_stmt->close();

                                $apponum = 1;
                                while (in_array($apponum, $booked_tokens)) {
                                    $apponum++;
                                }
                             }
                            ?>
                            <div class="booking-card" style="text-align:center; padding:50px;">
                                <div style="width:80px; height:80px; background:#4CAF50; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                                <h2>Payment Successful!</h2>
                                <p style="color:#666;">Your appointment is being confirmed...</p>
                                <br>
                                <form action="booking-complete.php" method="POST" id="finalForm">
                                    <input type="hidden" name="scheduleid" value="<?php echo $sid; ?>">
                                    <input type="hidden" name="slot_id"   value="<?php echo $slot_id; ?>">
                                    <input type="hidden" name="slot_num"  value="<?php echo $slot_num; ?>">
                                    <input type="hidden" name="apponum"   value="<?php echo $apponum; ?>">
                                    <input type="hidden" name="date"      value="<?php echo $today; ?>">
                                    <button type="submit" name="booknow" class="proceed-btn" style="padding:15px 40px;">Finalize Appointment &rarr;</button>
                                </form>
                            </div>
                            <?php
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * token-slots.php
 * Admin page: Manage doctor token limits and time slots per session.
 */

session_start();

// --- Auth Guard ---
if (!isset($_SESSION['user']) || $_SESSION['user'] === '' || $_SESSION['usertype'] !== 'a') {
    header('Location: ../login.php');
    exit;
}

include('../connection.php');

// --- Fetch all doctors for the selector ---
$all_doctors = $database->query("SELECT docid, docname FROM doctor ORDER BY docname ASC");

// --- Current selected doctor (from GET param) ---
$selected_docid = (int)($_GET['doc'] ?? 0);
$doctor         = null;
$schedules      = [];
$selected_sid   = (int)($_GET['sid'] ?? 0);
$slots          = [];

if ($selected_docid > 0) {
    // Fetch doctor details
    $dstmt = $database->prepare(
        "SELECT d.docid, d.docname, d.doc_max_tokens, d.doc_slot_duration, s.sname AS specialty
         FROM doctor d
         LEFT JOIN specialties s ON d.specialties = s.id
         WHERE d.docid = ?"
    );
    $dstmt->bind_param('i', $selected_docid);
    $dstmt->execute();
    $doctor = $dstmt->get_result()->fetch_assoc();

    // Fetch upcoming sessions for this doctor
    $sstmt = $database->prepare(
        "SELECT scheduleid, title, scheduledate, scheduletime, nop
         FROM schedule
         WHERE docid = ? AND scheduledate >= CURDATE()
         ORDER BY scheduledate ASC"
    );
    $sstmt->bind_param('i', $selected_docid);
    $sstmt->execute();
    $schedules = $sstmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch slots for selected session
    if ($selected_sid > 0) {
        $tsstmt = $database->prepare(
            "SELECT t.slot_id, t.slot_number, t.slot_time, t.is_booked,
                    p.pname AS patient_name
             FROM timeslots t
             LEFT JOIN appointment a ON a.slot_id = t.slot_id
             LEFT JOIN patient p ON p.pid = a.pid
             WHERE t.scheduleid = ?
             ORDER BY t.slot_number ASC"
        );
        $tsstmt->bind_param('i', $selected_sid);
        $tsstmt->execute();
        $slots = $tsstmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// --- Feedback message ---
$status  = $_GET['status'] ?? '';
$message = htmlspecialchars(urldecode($_GET['msg'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <title>Token & Slot Manager</title>
    <style>
        /* ── Page-specific styles ───────────────────────── */
        .ts-grid          { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; margin: 24px 40px; }
        .ts-card          { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
        .ts-card h3       { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; border-bottom: 2px solid #0a76d8; padding-bottom: 8px; }
        .ts-label         { font-size: 13px; color: #555; font-weight: 600; display: block; margin-bottom: 4px; }
        .ts-input         { width: 100%; padding: 9px 12px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; margin-bottom: 12px; box-sizing: border-box; }
        .ts-input:focus   { outline: none; border-color: #0a76d8; box-shadow: 0 0 0 3px rgba(10,118,216,.15); }
        .ts-btn           { padding: 9px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: opacity .2s; }
        .ts-btn:hover     { opacity: .85; }
        .ts-btn-primary   { background: #0a76d8; color: #fff; }
        .ts-btn-success   { background: #28a745; color: #fff; }
        .ts-btn-danger    { background: #dc3545; color: #fff; padding: 6px 14px; font-size: 12px; }
        .ts-btn-warning   { background: #f59e0b; color: #fff; padding: 6px 14px; font-size: 12px; }
        .ts-select        { width: 100%; padding: 9px 12px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
        .alert            { padding: 12px 16px; border-radius: 8px; margin: 0 40px 16px; font-size: 14px; font-weight: 500; }
        .alert-success    { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert-error      { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .slot-table       { width: 100%; border-collapse: collapse; font-size: 13px; }
        .slot-table th    { background: #f0f7ff; padding: 10px 12px; text-align: left; color: #374151; font-weight: 700; border-bottom: 2px solid #dbe4f0; }
        .slot-table td    { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .slot-table tr:hover td { background: #fafcff; }
        .badge            { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-free       { background: #dcfce7; color: #166534; }
        .badge-booked     { background: #fee2e2; color: #991b1b; }
        .inline-form      { display: inline; }
        .doc-meta         { display: flex; gap: 20px; background: #f0f7ff; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; }
        .doc-meta span    { color: #555; }
        .doc-meta b       { color: #0a76d8; }
        .session-row      { padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; margin-bottom: 8px; transition: all .2s; }
        .session-row:hover,
        .session-row.active { border-color: #0a76d8; background: #f0f7ff; }
        .session-row .s-title { font-weight: 600; font-size: 14px; }
        .session-row .s-meta  { font-size: 12px; color: #777; margin-top: 2px; }
        .page-header      { display: flex; align-items: center; gap: 16px; padding: 20px 40px 0; }
        .page-header h2   { font-size: 22px; font-weight: 700; color: #1a1a2e; }
        .section-sep      { margin: 20px 0 14px; font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; }
    </style>
</head>
<body>
<?php include('../connection.php'); /* already included but safe */ ?>

<!-- ── Sidebar Navigation ───────────────────────────────── -->
<div class="container">
    <div class="menu">
        <table class="menu-container" border="0">
            <tr>
                <td style="padding:10px" colspan="2">
                    <table border="0" class="profile-container">
                        <tr>
                            <td width="30%" style="padding-left:20px">
                                <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                            </td>
                            <td>
                                <p class="profile-title">Administrator</p>
                                <p class="profile-subtitle"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-dashbord">
                    <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></div></a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-doctor">
                    <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></div></a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-schedule">
                    <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-appoinment">
                    <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></div></a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-patient menu-active">
                    <a href="token-slots.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Token &amp; Slots</p></div></a>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Main Body ─────────────────────────────────────── -->
    <div class="dash-body">

        <!-- Page Header -->
        <div class="page-header">
            <h2>&#128197; Token &amp; Slot Manager</h2>
        </div>

        <!-- Feedback Alert -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- ── Step 1: Select Doctor ──────────────────────── -->
        <div style="margin: 20px 40px 0;">
            <form method="GET" action="token-slots.php" style="display:flex; gap:12px; align-items:flex-end;">
                <div style="flex:1;">
                    <label class="ts-label">Select Doctor to Manage</label>
                    <select name="doc" class="ts-select" required onchange="this.form.submit()">
                        <option value="">-- Choose a Doctor --</option>
                        <?php
                        $all_doctors->data_seek(0);
                        while ($d = $all_doctors->fetch_assoc()):
                        ?>
                            <option value="<?php echo $d['docid']; ?>"
                                <?php echo $selected_docid == $d['docid'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['docname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($doctor): ?>
        <!-- ── Doctor selected: show management interface ─── -->

        <!-- Doctor Meta Info -->
        <div style="margin: 0 40px;">
            <div class="doc-meta">
                <span>Doctor: <b><?php echo htmlspecialchars($doctor['docname']); ?></b></span>
                <span>Specialty: <b><?php echo htmlspecialchars($doctor['specialty'] ?? 'N/A'); ?></b></span>
                <span>Current Token Limit: <b><?php echo $doctor['doc_max_tokens']; ?> / session</b></span>
                <span>Slot Duration: <b><?php echo $doctor['doc_slot_duration']; ?> mins</b></span>
            </div>
        </div>

        <div class="ts-grid">

            <!-- LEFT PANEL: Token Settings + Sessions -->
            <div>
                <!-- ── Form 1: Update Token Limit ─────────── -->
                <div class="ts-card">
                    <h3>⚙️ Token Limit Settings</h3>
                    <form method="POST" action="slot-actions.php">
                        <input type="hidden" name="action" value="update_token_limit">
                        <input type="hidden" name="docid"  value="<?php echo $selected_docid; ?>">

                        <label class="ts-label" for="max_tokens">Max Tokens Per Session</label>
                        <input type="number" id="max_tokens" name="max_tokens" class="ts-input"
                               value="<?php echo (int)$doctor['doc_max_tokens']; ?>"
                               min="1" max="200" required
                               placeholder="e.g. 20">

                        <label class="ts-label" for="slot_duration">Slot Duration (minutes)</label>
                        <input type="number" id="slot_duration" name="slot_duration" class="ts-input"
                               value="<?php echo (int)$doctor['doc_slot_duration']; ?>"
                               min="5" max="120" required
                               placeholder="e.g. 15">

                        <small style="color:#6b7280; display:block; margin-bottom:14px;">
                            A token limit of <strong><?php echo $doctor['doc_max_tokens']; ?></strong>
                            × <strong><?php echo $doctor['doc_slot_duration']; ?> min</strong> =
                            <strong><?php echo $doctor['doc_max_tokens'] * $doctor['doc_slot_duration']; ?> mins</strong>
                            total session length.
                        </small>

                        <button type="submit" class="ts-btn ts-btn-primary">Save Token Settings</button>
                    </form>
                </div>

                <!-- ── Upcoming Sessions List ─────────────── -->
                <div class="ts-card" style="margin-top: 20px;">
                    <h3>📋 Upcoming Sessions</h3>
                    <?php if (empty($schedules)): ?>
                        <p style="color:#999; font-size:13px;">No upcoming sessions for this doctor.</p>
                    <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                            <a href="token-slots.php?doc=<?php echo $selected_docid; ?>&sid=<?php echo $s['scheduleid']; ?>"
                               style="text-decoration:none;">
                                <div class="session-row <?php echo $selected_sid == $s['scheduleid'] ? 'active' : ''; ?>">
                                    <div class="s-title"><?php echo htmlspecialchars($s['title']); ?></div>
                                    <div class="s-meta">
                                        📅 <?php echo $s['scheduledate']; ?>
                                        &nbsp;⏰ <?php echo date('h:i A', strtotime($s['scheduletime'])); ?>
                                        &nbsp;🎫 Limit: <?php echo $s['nop']; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT PANEL: Slot Management -->
            <div>
                <?php if ($selected_sid > 0): ?>

                    <!-- Selected session info bar -->
                    <?php
                    $sel_session = array_values(array_filter($schedules, fn($s) => $s['scheduleid'] == $selected_sid))[0] ?? null;
                    if ($sel_session):
                    ?>
                    <div style="background:#e0f2fe; border-radius:8px; padding:10px 16px; margin-bottom:16px; font-size:13px;">
                        Managing: <b><?php echo htmlspecialchars($sel_session['title']); ?></b>
                        on <b><?php echo $sel_session['scheduledate']; ?></b>
                        starting <b><?php echo date('h:i A', strtotime($sel_session['scheduletime'])); ?></b>
                    </div>
                    <?php endif; ?>

                    <!-- ── Auto-Generate Slots ──────────────── -->
                    <div class="ts-card" style="margin-bottom:20px;">
                        <h3>⚡ Auto-Generate All Slots</h3>
                        <p style="font-size:13px; color:#555; margin-bottom:14px;">
                            This will create <b><?php echo $doctor['doc_max_tokens']; ?> slots</b>
                            (every <?php echo $doctor['doc_slot_duration']; ?> mins), replacing any
                            existing unbooked slots.
                        </p>
                        <form method="POST" action="slot-actions.php"
                              onsubmit="return confirm('Auto-generate slots? Existing unbooked slots will be replaced.');">
                            <input type="hidden" name="action"     value="auto_generate_slots">
                            <input type="hidden" name="scheduleid" value="<?php echo $selected_sid; ?>">
                            <input type="hidden" name="docid"      value="<?php echo $selected_docid; ?>">
                            <button type="submit" class="ts-btn ts-btn-success">⚡ Generate Slots for This Session</button>
                        </form>
                    </div>

                    <!-- ── Existing Slots Table ──────────────── -->
                    <div class="ts-card" style="margin-bottom:20px;">
                        <h3>🕐 Existing Time Slots (<?php echo count($slots); ?> total)</h3>
                        <?php if (empty($slots)): ?>
                            <p style="color:#999; font-size:13px;">No slots yet. Use Auto-Generate above or add manually below.</p>
                        <?php else: ?>
                            <table class="slot-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Token</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Patient</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($slots as $slot): ?>
                                    <tr>
                                        <td style="color:#999;"><?php echo $slot['slot_id']; ?></td>
                                        <td><b>#<?php echo $slot['slot_number']; ?></b></td>
                                        <td>
                                            <?php if (!$slot['is_booked']): ?>
                                                <!-- Inline edit form -->
                                                <form method="POST" action="slot-actions.php" class="inline-form">
                                                    <input type="hidden" name="action"  value="update_slot">
                                                    <input type="hidden" name="slot_id" value="<?php echo $slot['slot_id']; ?>">
                                                    <input type="hidden" name="docid"   value="<?php echo $selected_docid; ?>">
                                                    <input type="time" name="slot_time"
                                                           value="<?php echo substr($slot['slot_time'],0,5); ?>"
                                                           class="ts-input" style="width:100px; margin:0; display:inline-block;" required>
                                                    <button type="submit" class="ts-btn ts-btn-warning">Save</button>
                                                </form>
                                            <?php else: ?>
                                                <b><?php echo date('h:i A', strtotime($slot['slot_time'])); ?></b>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $slot['is_booked'] ? 'booked' : 'free'; ?>">
                                                <?php echo $slot['is_booked'] ? 'Booked' : 'Free'; ?>
                                            </span>
                                        </td>
                                        <td style="font-size:12px; color:#555;">
                                            <?php echo htmlspecialchars($slot['patient_name'] ?? '—'); ?>
                                        </td>
                                        <td>
                                            <?php if (!$slot['is_booked']): ?>
                                                <form method="POST" action="slot-actions.php" class="inline-form"
                                                      onsubmit="return confirm('Delete slot #<?php echo $slot['slot_number']; ?>?');">
                                                    <input type="hidden" name="action"  value="delete_slot">
                                                    <input type="hidden" name="slot_id" value="<?php echo $slot['slot_id']; ?>">
                                                    <input type="hidden" name="docid"   value="<?php echo $selected_docid; ?>">
                                                    <button type="submit" class="ts-btn ts-btn-danger">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:#aaa; font-size:12px;">Locked</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- ── Add Single Slot Manually ─────────── -->
                    <div class="ts-card">
                        <h3>➕ Add Slot Manually</h3>
                        <form method="POST" action="slot-actions.php" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                            <input type="hidden" name="action"     value="create_slot">
                            <input type="hidden" name="scheduleid" value="<?php echo $selected_sid; ?>">
                            <input type="hidden" name="docid"      value="<?php echo $selected_docid; ?>">

                            <div>
                                <label class="ts-label">Token Number</label>
                                <input type="number" name="slot_number" class="ts-input"
                                       style="width:100px;" min="1" max="500"
                                       placeholder="e.g. 21" required>
                            </div>
                            <div>
                                <label class="ts-label">Slot Time</label>
                                <input type="time" name="slot_time" class="ts-input"
                                       style="width:130px;" required>
                            </div>
                            <div>
                                <button type="submit" class="ts-btn ts-btn-primary" style="margin-bottom:12px;">
                                    Add Slot
                                </button>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- No session selected yet -->
                    <div class="ts-card" style="text-align:center; padding:50px;">
                        <div style="font-size:48px; margin-bottom:16px;">📋</div>
                        <p style="color:#6b7280;">Select an upcoming session on the left to manage its time slots.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /.ts-grid -->

        <?php else: ?>
            <!-- No doctor selected -->
            <div style="margin:40px; text-align:center; padding:60px; background:#f9f9f9; border-radius:12px;">
                <div style="font-size:56px; margin-bottom:16px;">👆</div>
                <p style="color:#6b7280; font-size:16px;">Select a doctor above to manage their token limits and time slots.</p>
            </div>
        <?php endif; ?>

    </div><!-- /.dash-body -->
</div><!-- /.container -->

</body>
</html>

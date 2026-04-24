<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] === '' || $_SESSION['usertype'] !== 'a') {
    header('Location: ../login.php'); exit;
}
$admin_email = $_SESSION['user'];
include('../connection.php');

// Fetch admin row (for profile image)
$astmt = $database->prepare("SELECT aemail, profile_image FROM admin WHERE aemail = ?");
$astmt->bind_param('s', $admin_email);
$astmt->execute();
$admin = $astmt->get_result()->fetch_assoc();
$avatar_src = !empty($admin['profile_image'])
    ? '../' . htmlspecialchars($admin['profile_image'])
    : '../img/user.png';

date_default_timezone_set('Asia/kathmandu');
$today = date('Y-m-d');

// Handle password change
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $new_pass  = trim($_POST['new_password'] ?? '');
    $conf_pass = trim($_POST['confirm_password'] ?? '');
    if ($new_pass === '') {
        $msg = ['type' => 'error', 'text' => 'Password cannot be empty.'];
    } elseif ($new_pass !== $conf_pass) {
        $msg = ['type' => 'error', 'text' => 'Passwords do not match.'];
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $database->prepare("UPDATE admin SET apassword = ? WHERE aemail = ?");
        $upd->bind_param('ss', $hashed, $admin_email);
        $upd->execute();
        $msg = ['type' => 'success', 'text' => 'Password updated successfully!'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <title>Admin Settings</title>
    <style>
        .dashbord-tables{ animation: transitionIn-Y-over 0.5s; }
        .filter-container{ animation: transitionIn-X  0.5s; }
        .sub-table{ animation: transitionIn-Y-bottom 0.5s; }

        /* Avatar upload */
        .avatar-wrap { position:relative; width:70px; height:70px; border-radius:50%; overflow:hidden; cursor:pointer; margin:0 auto 4px; }
        .avatar-wrap img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
        .avatar-overlay { position:absolute; inset:0; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .2s; border-radius:50%; }
        .avatar-wrap:hover .avatar-overlay { opacity:1; }
        .avatar-overlay svg { width:22px; height:22px; stroke:#fff; }
        #avatarInput { display:none; }
        .upload-toast { position:fixed; bottom:24px; right:24px; z-index:9999; background:#0a76d8; color:#fff; padding:12px 22px; border-radius:10px; font-size:14px; font-weight:600; box-shadow:0 4px 20px rgba(0,118,216,.35); display:none; }

        /* Settings card */
        .settings-section { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); padding:30px; margin:20px 0; max-width:480px; }
        .settings-section h3 { margin:0 0 20px; color:#0a76d8; font-size:18px; }
        .settings-section label { font-size:13px; font-weight:600; color:#444; display:block; margin-bottom:6px; }
        .settings-section input[type="password"] { width:100%; padding:10px 14px; border:1px solid #dde3f0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-bottom:14px; }
        .settings-section button[type="submit"] { background:#0a76d8; color:#fff; border:none; padding:12px 28px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
        .msg-box { padding:10px 16px; border-radius:8px; font-size:13px; font-weight:600; margin-bottom:16px; }
        .msg-error   { background:#fee2e2; color:#991b1b; }
        .msg-success { background:#dcfce7; color:#166534; }

        /* Avatar card */
        .avatar-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); padding:30px; margin:20px 0; max-width:480px; display:flex; align-items:center; gap:24px; }
        .avatar-card-info h3 { margin:0 0 6px; font-size:16px; }
        .avatar-card-info p { margin:0; color:#888; font-size:13px; }
        .avatar-change-btn { display:inline-block; margin-top:10px; background:#0a76d8; color:#fff; padding:8px 18px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; border:none; }
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
                            <td width="30%" style="padding-left:20px">
                                <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()" title="Click to change photo">
                                    <img src="<?php echo $avatar_src; ?>" alt="Admin" id="avatarPreview">
                                    <div class="avatar-overlay">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </div>
                                </div>
                                <input type="file" id="avatarInput" accept="image/*"
                                       onchange="uploadAvatar(this, 'admin', 0)">
                            </td>
                            <td style="padding:0px;margin:0px;">
                                <p class="profile-title">Admin</p>
                                <p class="profile-subtitle"><?php echo substr($admin_email, 0, 22); ?></p>
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
                <td class="menu-btn menu-icon-patient">
                    <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></div></a>
                </td>
            </tr>
            <tr class="menu-row menu-active">
                <td class="menu-btn menu-icon-settings menu-active menu-icon-settings-active">
                    <a href="settings.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Settings</p></div></a>
                </td>
            </tr>
        </table>
    </div>

    <div class="dash-body" style="margin-top:15px; padding:20px 30px;">
        <h2 style="font-size:23px; font-weight:600; margin-bottom:4px;">Admin Settings</h2>
        <p style="color:#888; font-size:13px; margin-bottom:20px;">Manage your profile photo and account password.</p>

        <!-- Profile Photo Card -->
        <div class="avatar-card">
            <div class="avatar-wrap" onclick="document.getElementById('avatarInput2').click()" style="width:90px;height:90px;flex-shrink:0;" title="Click to change">
                <img src="<?php echo $avatar_src; ?>" alt="Admin" id="avatarPreview2" style="width:90px;height:90px;">
                <div class="avatar-overlay">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
            </div>
            <input type="file" id="avatarInput2" accept="image/*"
                   onchange="uploadAvatarMulti(this, 'admin', 0)">
            <div class="avatar-card-info">
                <h3>Profile Photo</h3>
                <p>JPG, PNG, GIF or WebP &bull; Max 3 MB</p>
                <button class="avatar-change-btn" onclick="document.getElementById('avatarInput2').click()">
                    📷 Change Photo
                </button>
            </div>
        </div>

        <!-- Change Password -->
        <div class="settings-section">
            <h3>🔑 Change Password</h3>

            <?php if ($msg): ?>
            <div class="msg-box msg-<?php echo $msg['type']; ?>"><?php echo htmlspecialchars($msg['text']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button type="submit" name="change_password">Update Password</button>
            </form>
        </div>

        <!-- Account Info -->
        <div class="settings-section">
            <h3>ℹ️ Account Info</h3>
            <p style="font-size:14px;"><b>Email:</b> <?php echo htmlspecialchars($admin_email); ?></p>
            <p style="font-size:14px;"><b>Role:</b> Administrator</p>
        </div>
    </div>
</div>

<div class="upload-toast" id="uploadToast"></div>

<script>
function uploadAvatar(input, role, userId) {
    uploadFile(input, role, userId, 'avatarPreview');
}
function uploadAvatarMulti(input, role, userId) {
    uploadFile(input, role, userId, 'avatarPreview');
    uploadFile(input, role, userId, 'avatarPreview2');
}
function uploadFile(input, role, userId, previewId) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    var reader = new FileReader();
    reader.onload = function(e) {
        var el = document.getElementById(previewId);
        if (el) el.src = e.target.result;
    };
    reader.readAsDataURL(file);

    var form = new FormData();
    form.append('avatar',   file);
    form.append('role',     role);
    form.append('user_id',  userId);

    showToast('⏳ Uploading...');
    fetch('upload-avatar.php', { method:'POST', body:form })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                showToast('✅ ' + d.message);
            } else {
                showToast('❌ ' + (d.error || 'Upload failed.'));
                ['avatarPreview','avatarPreview2'].forEach(function(id){
                    var el = document.getElementById(id);
                    if (el) el.src = '<?php echo $avatar_src; ?>';
                });
            }
        })
        .catch(function(){ showToast('❌ Network error.'); });
}
function showToast(msg) {
    var t = document.getElementById('uploadToast');
    t.textContent = msg; t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(function(){ t.style.display='none'; }, 3500);
}
</script>
</body>
</html>

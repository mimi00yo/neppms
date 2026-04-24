<?php
session_start();
include('../connection.php');

if (!$_POST) {
    header('location: settings.php');
    exit;
}

$id       = (int)$_POST['id00'];
$name     = trim($_POST['name']);
$spec     = (int)$_POST['spec'];
$oldemail = trim($_POST['oldemail']);
$email    = trim($_POST['email']);
$tele      = trim($_POST['Tele']);
$password  = $_POST['password'];
$cpassword = $_POST['cpassword'];

if (!preg_match("/^(98|97)\d{8}$/", $tele)) {
    header("location: settings.php?action=edit&error=5&id=$id");
    exit;
}

if ($password !== $cpassword) {
    header("location: settings.php?action=edit&error=2&id=$id");
    exit;
}

file_put_contents('debug.txt', "POST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true));

// Check email collision (another doctor already owns this email)
$chk = $database->prepare("SELECT docid FROM doctor WHERE docemail = ? AND docid != ?");
$chk->bind_param('si', $email, $id);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    header("location: settings.php?action=edit&error=1&id=$id");
    exit;
}

// ── Handle profile image upload (optional) ────────────────────────────────
$profile_image_sql = '';
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['profile_image'];
    $max_size = 3 * 1024 * 1024;
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_exts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['size'] <= $max_size && in_array($mime, $allowed_types) && in_array($ext, $allowed_exts)) {
        $upload_dir = __DIR__ . '/../img/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Delete old image
        $old = $database->prepare("SELECT profile_image FROM doctor WHERE docid = ?");
        $old->bind_param('i', $id);
        $old->execute();
        $old_img = $old->get_result()->fetch_assoc()['profile_image'] ?? null;
        if ($old_img && strpos($old_img, 'img/uploads/') !== false) {
            @unlink(__DIR__ . '/../' . $old_img);
        }

        $filename = 'doctor_' . $id . '_' . time() . '.' . $ext;
        $dest     = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $web_path = 'img/uploads/' . $filename;
            $profile_image_sql = ", profile_image = '$web_path'";
        }
    }
}

// ── Update doctor record ──────────────────────────────────────────────────
$sql = "UPDATE doctor
        SET docemail='$email', docname='$name', docpassword='$password',
            doctel='$tele', specialties=$spec
            $profile_image_sql
        WHERE docid=$id";
$database->query($sql);

// Update webuser email if changed
if ($oldemail !== $email) {
    $database->query("UPDATE webuser SET email='$email' WHERE email='$oldemail'");
}

header("location: settings.php?action=edit&error=4&id=$id");
exit;
?>
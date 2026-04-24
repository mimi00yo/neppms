<?php
session_start();
include("../connection.php");

if($_POST){
    $name=$_POST['name'];
    $oldemail=$_POST["oldemail"];
    $spec=(int)$_POST['spec'];
    $email=$_POST['email'];
    $tele=$_POST['Tele'];
    $password=$_POST['password'];
    $cpassword=$_POST['cpassword'];
    $id=(int)$_POST['id00'];
    
    if (!preg_match("/^(98|97)\d{8}$/", $tele)) {
        $error = '5';
    } elseif ($password==$cpassword){
        $error='3';
        $result = $database->query("select doctor.docid from doctor inner join webuser on doctor.docemail=webuser.email where webuser.email='$email';");
        
        if($result->num_rows==1){
            $id2=$result->fetch_assoc()["docid"];
        }else{
            $id2=$id;
        }
        
        if($id2!=$id){
            $error='1';
        }else{
            $max_tokens = isset($_POST['max_tokens']) ? (int)$_POST['max_tokens'] : null;
            $slot_duration = isset($_POST['slot_duration']) ? (int)$_POST['slot_duration'] : null;
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];

            // Handle optional profile image upload
            $profile_image_sql = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_image'];
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

            // Build update query correctly depending on max_tokens existence
            if ($max_tokens !== null && $slot_duration !== null) {
                $sql1 = "update doctor set docemail='$email',docname='$name',docpassword='$password',doctel='$tele',specialties=$spec,doc_max_tokens=$max_tokens,doc_slot_duration=$slot_duration,doc_start_time='$start_time',doc_end_time='$end_time' $profile_image_sql where docid=$id";
            } else {
                $sql1 = "update doctor set docemail='$email',docname='$name',docpassword='$password',doctel='$tele',specialties=$spec,doc_start_time='$start_time',doc_end_time='$end_time' $profile_image_sql where docid=$id";
            }

            $database->query($sql1);
            if ($database->error) {
                echo "DB Error: " . $database->error . "\n";
                echo "Query: " . $sql1 . "\n";
                exit;
            }
            
            $sql2 = "update webuser set email='$email' where email='$oldemail'";
            $database->query($sql2);
            
            $error = '4';
        }
    }else{
        $error='2';
    }
}else{
    $error='3';
}

header("location: doctors.php?action=edit&error=".$error."&id=".$id);
?>
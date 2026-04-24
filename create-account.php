<?php
    session_start();
    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    date_default_timezone_set('Asia/kathmandu');
    $date = date('Y-m-d');
    $_SESSION["date"]=$date;

    include("connection.php");

    $error = '';

    if($_POST){
        $fname=$_SESSION['personal']['fname'];
        $lname=$_SESSION['personal']['lname'];
        $name=$fname." ".$lname;
        $address=$_SESSION['personal']['address'];
        $dob=$_SESSION['personal']['dob'];
        $email=$_POST['newemail'];
        $tele=$_POST['tele'];
        $newpassword=$_POST['newpassword'];
        $cpassword=$_POST['cpassword'];
        
        if (!preg_match("/^(98|97)\d{8}$/", $tele)) {
            $error = 'Invalid telephone number. Please use Nepal mobile format (98XXXXXXXX or 97XXXXXXXX).';
        } elseif ($newpassword==$cpassword){
            $sqlmain= "select * from webuser where email=?;";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows==1){
                $error= 'Already have an account for this email address.';
            }else{
                $database->query("insert into patient(pemail,pname,ppassword, paddress, pdob,ptel) values('$email','$name','$newpassword','$address','$dob','$tele');");
                $database->query("insert into webuser values('$email','p')");
                $_SESSION["user"]=$email;
                $_SESSION["usertype"]="p";
                $_SESSION["username"]=$fname;
                header('Location: patient/index.php');
            }
        }else{
            $error= 'Password confirmation error! Please re-confirm password.';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Account | MedCare ✨</title>
    <link rel="stylesheet" href="css/animations.css">  
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #0A76D8;
            --primary-soft: #D8EBFA;
            --bg-cute: #f0f7ff;
            --card-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            --glass-bg: rgba(255, 255, 255, 0.85);
        }

        * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            background: var(--bg-cute); 
            background-image: 
                radial-gradient(at 0% 0%, rgba(10, 118, 216, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(10, 118, 216, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        .mascot-container {
            position: absolute;
            bottom: -30px;
            right: 5%;
            width: 320px;
            z-index: 0;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .account-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            width: 100%;
            max-width: 500px;
            padding: 40px 50px;
            border-radius: 40px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.4);
            z-index: 1;
            position: relative;
        }

        .brand-section { text-align: center; margin-bottom: 30px; }
        .brand-section h1 { font-size: 28px; font-weight: 800; color: #1e1b4b; margin-bottom: 8px; }
        .brand-section p { color: #64748b; font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding-left: 5px; }
        
        .input-control {
            width: 100%;
            padding: 14px 20px;
            border-radius: 18px;
            border: 2px solid #eef2f6;
            background: #fff;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .input-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-soft);
            outline: none;
        }

        .error-msg {
            background: #fff1f2;
            color: #e11d48;
            padding: 12px 18px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #ffe4e6;
            display: flex; align-items: center; gap: 8px;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 20px;
            background: var(--primary);
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(10, 118, 216, 0.4);
            margin-top: 10px;
        }
        .submit-btn:hover {
            background: #0966bc;
            transform: translateY(-2px);
        }

        .footer-text { text-align: center; margin-top: 25px; font-size: 14px; color: #64748b; font-weight: 500; }
        .footer-text a { color: var(--primary); text-decoration: none; font-weight: 800; }
    </style>
</head>
<body>
    <div class="mascot-container">
        <img src="cute_medical_mascot_signup_1776892963243.png" alt="Mascot" width="100%">
    </div>

    <div class="account-card">
        <div class="brand-section">
            <h1>Almost There! ✨</h1>
            <p>Step 2: Setup your account</p>
        </div>

        <form action="" method="POST">
            <?php if(!empty($error)): ?>
                <div class="error-msg">
                    <span>⚠️</span> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="newemail" class="input-control" placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label>Mobile Number</label>
                <input type="tel" name="tele" class="input-control" placeholder="98XXXXXXXX" pattern="(98|97)[0-9]{8}" title="Please enter a valid Nepal mobile number (10 digits starting with 98 or 97)" required>
            </div>

            <div class="form-group">
                <label>Create Password</label>
                <input type="password" name="newpassword" class="input-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="cpassword" class="input-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="submit-btn">
                Complete Sign Up
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
        </form>

        <div class="footer-text">
            Need to change details? <a href="signup.php">Go back</a>
        </div>
    </div>
</body>
</html>
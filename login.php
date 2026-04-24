<?php
    session_start();
    $_SESSION["user"]="";
    $_SESSION["usertype"]="";

    date_default_timezone_set('Asia/kathmandu');
    $date = date('Y-m-d');
    $_SESSION["date"]=$date;

    include("connection.php");

    $error_msg = '';

    if($_POST){
        $email    = $_POST['useremail'];
        $password = $_POST['userpassword'];

        $result = $database->query("select * from webuser where email='$email'");
        if($result->num_rows == 1){
            $utype = $result->fetch_assoc()['usertype'];

            if($utype == 'p'){
                $checker = $database->query("select * from patient where pemail='$email' and ppassword='$password'");
                if($checker->num_rows == 1){
                    $_SESSION['user']    = $email;
                    $_SESSION['usertype']= 'p';
                    header('location: patient/index.php');
                } else {
                    $error_msg = 'Wrong credentials: Invalid email or password.';
                }

            } elseif($utype == 'a'){
                $checker = $database->query("select * from admin where aemail='$email' and apassword='$password'");
                if($checker->num_rows == 1){
                    $_SESSION['user']    = $email;
                    $_SESSION['usertype']= 'a';
                    header('location: admin/index.php');
                } else {
                    $error_msg = 'Wrong credentials: Invalid email or password.';
                }

            } elseif($utype == 'd'){
                $checker = $database->query("select * from doctor where docemail='$email' and docpassword='$password'");
                if($checker->num_rows == 1){
                    $_SESSION['user']    = $email;
                    $_SESSION['usertype']= 'd';
                    header('location: doctor/index.php');
                } else {
                    $error_msg = 'Wrong credentials: Invalid email or password.';
                }
            }

        } else {
            $error_msg = 'No account found for this email address.';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MedCare ✨</title>
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
        }



        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            width: 100%;
            max-width: 450px;
            padding: 50px;
            border-radius: 40px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.4);
            z-index: 1;
            position: relative;
        }

        .brand-section { text-align: center; margin-bottom: 40px; }
        .brand-section h1 { font-size: 32px; font-weight: 800; color: #1e1b4b; margin-bottom: 8px; }
        .brand-section p { color: #64748b; font-size: 15px; font-weight: 500; }

        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; padding-left: 5px; }
        
        .input-control {
            width: 100%;
            padding: 16px 24px;
            border-radius: 20px;
            border: 2px solid #eef2f6;
            background: #fff;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 5px var(--primary-soft);
            outline: none;
            transform: scale(1.01);
        }

        .error-msg {
            background: #fff1f2;
            color: #e11d48;
            padding: 15px 20px;
            border-radius: 18px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid #ffe4e6;
            display: flex; align-items: center; gap: 10px;
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
            box-shadow: 0 10px 20px -5px rgba(10, 118, 216, 0.4);
        }
        .submit-btn:hover {
            background: #0966bc;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -10px rgba(10, 118, 216, 0.5);
        }
        .submit-btn:active { transform: translateY(0); }

        .footer-text { text-align: center; margin-top: 30px; font-size: 14px; color: #64748b; font-weight: 500; }
        .footer-text a { color: var(--primary); text-decoration: none; font-weight: 800; transition: all 0.2s; }
        .footer-text a:hover { opacity: 0.7; }

        .back-home {
            position: absolute;
            top: 40px;
            left: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #64748b;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s;
        }
        .back-home:hover { color: var(--primary); }
    </style>
</head>
<body>
    <a href="index.html" class="back-home">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Back to Home
    </a>



    <div class="login-card">
        <div class="brand-section">
            <h1>Welcome Back! ✨</h1>
            <p>Nice to see you again. Please sign in.</p>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label for="useremail">Email Address</label>
                <input type="email" name="useremail" class="input-control" placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="userpassword">Password</label>
                <input type="password" name="userpassword" class="input-control" placeholder="••••••••" required>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="error-msg">
                    <span>⚠️</span> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="submit-btn">Sign In</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="signup.php">Create one now</a>
        </div>
    </div>
</body>
</html>
<?php
    session_start();
    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    date_default_timezone_set('Asia/kathmandu');
    $date = date('Y-m-d');
    $_SESSION["date"]=$date;

    if($_POST){
        $_SESSION["personal"]=array(
            'fname'=>$_POST['fname'],
            'lname'=>$_POST['lname'],
            'address'=>$_POST['address'],
            'dob'=>$_POST['dob']
        );
        header("location: create-account.php");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | MedCare ✨</title>
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
            top: -20px;
            left: 5%;
            width: 350px;
            z-index: 0;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .signup-card {
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

        .brand-section { text-align: center; margin-bottom: 35px; }
        .brand-section h1 { font-size: 30px; font-weight: 800; color: #1e1b4b; margin-bottom: 8px; }
        .brand-section p { color: #64748b; font-size: 14px; font-weight: 500; }

        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; margin-bottom: 20px; }
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
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-home">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Back to Home
    </a>

    <div class="mascot-container">
        <img src="cute_nurse_mascot_signup_1776892999194.png" alt="Mascot" width="100%">
    </div>

    <div class="signup-card">
        <div class="brand-section">
            <h1>Let's Get Started! ✨</h1>
            <p>Step 1: Your personal details</p>
        </div>

        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="fname" class="input-control" placeholder="John" required autofocus>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lname" class="input-control" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="input-control" placeholder="Street Address" required>
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="input-control" required>
            </div>

            <button type="submit" class="submit-btn">
                Next Step
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

        <div class="footer-text">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
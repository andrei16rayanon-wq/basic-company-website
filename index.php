<?php
session_start();
require_once 'db_connect.php';

$error = "";
$message = "";

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'registered') { $message = "REGISTRATION SUCCESSFUL."; }
    if ($_GET['status'] === 'logged_out') { $message = "SESSION TERMINATED SECURELY."; }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM employee WHERE Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['employee_id'] = $user['Id'];
            $_SESSION['username']    = $user['Username'];

            $stmtAtnd = $pdo->prepare("INSERT INTO attendance (employee_id, log_in_time, status) VALUES (?, NOW(), 'Online')");
            $stmtAtnd->execute([$user['Id']]);
            $_SESSION['attendance_id'] = $pdo->lastInsertId();

            header("Location: /registerform_paul/home.php");
            exit();
        } else {
            $error = "AUTHENTICATION FAILURE: ACCESS DENIED";
        }
    } catch (PDOException $e) {
        $error = "SYSTEM ERROR: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal.io | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Using your Home Page color variables */
        :root { 
            --bg: #0b0e11; 
            --card: #1e2329; 
            --green: #0ecb81; 
            --red: #f6465d; 
            --border: #2b3139; 
            --text: #eaecef; 
            --gray: #848e9c; 
        }

        body { 
            font-family: 'Roboto', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }

        .login-container {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo { 
            color: var(--green); 
            font-weight: bold; 
            font-size: 28px; 
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .subtitle {
            color: var(--gray);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-msg { 
            font-size: 13px; 
            padding: 12px; 
            border-radius: 4px; 
            text-align: center; 
            margin-bottom: 20px;
        }
        .msg-success { 
            background: rgba(14, 203, 129, 0.1); 
            color: var(--green); 
            border: 1px solid var(--green); 
        }
        .msg-error { 
            background: rgba(246, 70, 93, 0.1); 
            color: var(--red); 
            border: 1px solid var(--red); 
        }

        label { 
            font-size: 12px; 
            color: var(--gray); 
            display: block;
            margin-bottom: 8px;
        }

        input { 
            background: #0b0e11; 
            border: 1px solid var(--border); 
            color: white; 
            padding: 12px; 
            border-radius: 4px; 
            font-size: 16px; 
            width: 100%; 
            box-sizing: border-box; 
            margin-bottom: 20px;
        }

        input:focus {
            outline: none;
            border-color: var(--green);
        }

        .btn-login { 
            background: var(--green); 
            color: black; 
            border: none; 
            padding: 15px; 
            font-weight: bold; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%;
            font-size: 16px;
            transition: opacity 0.2s;
        }

        .btn-login:hover {
            opacity: 0.9;
        }

        .footer-links {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: var(--gray);
        }

        .footer-links a {
            color: var(--green);
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .security-note {
            margin-top: 30px;
            font-size: 11px;
            color: #444;
            text-align: center;
            border-top: 1px solid var(--border);
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="header">
        <a href="#" class="logo"><i class="fa-solid fa-chart-line"></i> TERMINAL.IO</a>
        <div class="subtitle">Secure Employee Gateway</div>
    </div>

    <?php if($message): ?>
        <div class="status-msg msg-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="status-msg msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>IDENTIFICATION / USERNAME</label>
            <input type="text" name="username" required autocomplete="username" placeholder="Enter username">
        </div>

        <div class="input-group">
            <label>ACCESS KEY / PASSWORD</label>
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-login">AUTHORIZE ACCESS</button>
    </form>

    <div class="footer-links">
        New to the terminal? <a href="register.php">Request Enrollment</a>
    </div>

    <div class="security-note">
        <i class="fa-solid fa-shield-halved"></i> 256-bit AES Encrypted Connection Active
    </div>
</div>

</body>
</html>
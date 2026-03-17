<?php
session_start();

// Define your admin password here
$admin_password = "your_secret_password";

if (isset($_POST['action']) && $_POST['action'] == 'login') {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect Password";
    }
}

// Logout logic - Redirects to index.php (the public gallery)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login - StoryHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* New S+F Factory Logo */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: #222;
            border: 2px solid #ffaa00;
            /* Safety Orange/Yellow */
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 20px rgba(255, 170, 0, 0.2);
        }

        /* Creating the S and F overlap look */
        .brand-icon::before {
            content: 'S';
            color: #ffaa00;
            font-weight: 900;
            font-size: 24px;
            position: absolute;
            left: 8px;
            top: 2px;
        }

        .brand-icon::after {
            content: 'F';
            color: #fff;
            font-weight: 900;
            font-size: 24px;
            position: absolute;
            right: 8px;
            bottom: 2px;
        }

        .brand-text {
            font-family: 'Orbitron', 'Segoe UI', sans-serif;
            /* A techy font if available */
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #eee;
        }

        .brand-text span {
            color: #ffaa00;
            font-weight: 900;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #000;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .gate-card {
            background: #111;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #333;
            text-align: center;
            width: 320px;
        }

        h1 {
            margin-bottom: 30px;
            font-weight: 300;
        }

        .btn-admin {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            background: #00aaff;
            color: white;
            font-size: 16px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .error {
            color: #ff4444;
            font-size: 13px;
            margin-top: 10px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 13px;
        }

        .back-link:hover {
            color: #aaa;
        }
    </style>
</head>

<body>
    <div class="gate-card">
        <h1>Admin Access</h1>
        <form method="post">
            <input type="password" name="password" placeholder="Admin Password" required autofocus>
            <button type="submit" name="action" value="login" class="btn btn-admin">Login</button>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        </form>
        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/" class="back-link">← Back to Gallery</a>
    </div>
</body>

</html>
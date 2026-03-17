<?php
session_start();

// Define your admin password here
$admin_password = "your_secret_password";

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'guest') {
        $_SESSION['role'] = 'guest';
        header("Location: index.php");
        exit;
    }

    if ($_POST['action'] == 'login') {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['role'] = 'admin';
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect Password";
        }
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: hub.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Welcome to StoryHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
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

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
        }

        .btn-guest {
            background: #333;
            color: white;
        }

        .btn-admin {
            background: #00aaff;
            color: white;
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
    </style>
</head>

<body>
    <div class="gate-card">
        <h1>StoryHub</h1>

        <form method="post">
            <button type="submit" name="action" value="guest" class="btn btn-guest">Enter as Guest</button>
        </form>

        <hr style="border: 0; border-top: 1px solid #222; margin: 30px 0;">

        <form method="post">
            <input type="password" name="password" placeholder="Admin Password" required>
            <button type="submit" name="action" value="login" class="btn btn-admin">Login as Admin</button>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        </form>
    </div>
</body>

</html>
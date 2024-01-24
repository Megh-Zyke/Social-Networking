<?php
include 'connect.php';

session_start();

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];  // Don't hash it here, do it later

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_user->execute([$email]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        // Verify password using password_verify function
        if (password_verify($password, $row['password'])) {
            if ($row['approved']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['first_name'];
                header('location: home.php');
                exit();
            } else {
                echo 'Need to be approved by admin';
            }
        } else {
            echo 'Incorrect username or password!';
        }
    } else {
        echo 'Incorrect username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="login.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <h1>Welcome Back !</h1>
            <div class="upload-btn-wrapper">
            </div>
        </div>
        <div class="right-side">
            <h2>Login </h2>

            <div class="error-message">
                <?php
                if (isset($error_message)) {
                    echo $error_message;
                }
                ?>
            </div>

            <form method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="submit" class="submit">Login</button>
                <div id="forgot-password">
                    <a href="#" class="forgot-password">Forgotten Password?</a>
                </div>
                <hr>
                <button type="button" class="create"><a href="register.php">Create Account</a></button>
            </form>
        </div>
    </div>
</body>
</html>

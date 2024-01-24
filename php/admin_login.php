<?php

include 'connect.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $password = sha1($_POST['password']);
   $password = filter_var($password, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE email = ? AND password = ?");
   $select_admin->execute([$email, $password]);
   
   if($select_admin->rowCount() > 0){
      $fetch_admin_id = $select_admin->fetch(PDO::FETCH_ASSOC);
      $_SESSION['admin_id'] = $fetch_admin_id['id'];
      header('location:admin_dashboard.php');
   }else{
      echo 'incorrect username or password!';
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
</head>
<body>
    
<?php if (isset($error_message)): ?>
        <p><?php echo $error_message; ?></p>
    <?php endif; ?>
    <div class="container">
        <div class="left-side">
            <h1>Welcome Back !</h1>
            <div class="upload-btn-wrapper">
            </div>
        </div>
        <div class="right-side">
            <h2>Login </h2>
            <form action="#" method="post">
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

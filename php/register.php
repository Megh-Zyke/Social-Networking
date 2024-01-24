<?php
include 'connect.php';

session_start();

if (isset($_POST['submit'])) {
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $date_of_birth = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);

    // Handle image upload
    $profile_image_url = uploadImage();

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_user->execute([$email]);

    if ($select_user->rowCount() > 0) {
        echo 'Email already exists!';
    } else {
        $insert_user = $conn->prepare("INSERT INTO `users` (first_name, last_name, email, password, date_of_birth, gender, profile_image_url, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_user->execute([$first_name, $last_name, $email, $password, $date_of_birth, $gender, $profile_image_url, 0]); // Set status to 0 initially

        if ($insert_user->rowCount() > 0) {
            $_SESSION['user_id'] = $conn->lastInsertId();
            
            // Check if the user needs admin approval
            if ($_POST['gender'] === 'custom') {
                header('Location: admin_panel.php?registration_success=1');
            } else {
                header('Location: registration_pending.php');
            }
            
            exit();
        } else {
            echo 'Registration failed. Please try again.';
        }
    }
}

function uploadImage() {
    $targetDir = "uploads/";
    $uploadOk = 1;

    if (isset($_FILES["fileInput"]) && $_FILES["fileInput"]["name"]) {
        $targetFile = $targetDir . basename($_FILES["fileInput"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["fileInput"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        if (file_exists($targetFile)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        if ($_FILES["fileInput"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedExtensions)) {
            echo  "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["fileInput"]["tmp_name"], $targetFile)) {
                echo "The file " . htmlspecialchars(basename($_FILES["fileInput"]["name"])) . " has been uploaded.";
                return $targetFile;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    return '';
}
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Page</title>
        <link href="register.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/2.7.0/cropper.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/2.7.0/cropper.min.css">    
    <head>
        <script>
            function populateDropdowns() {
                var dayDropdown = document.getElementsByName("day")[0];
                var monthDropdown = document.getElementsByName("month")[0];
                var yearDropdown = document.getElementsByName("year")[0];
            
                var currentYear = new Date().getFullYear();
                var currentMonth = new Date().getMonth() + 1;
                var currentDay = new Date().getDate();
            
                for (var i = 1; i <= 31; i++) {
                    var option = document.createElement("option");
                    option.value = i;
                    option.text = i;
                    dayDropdown.add(option);
                    if (i === currentDay) {
                        dayDropdown.selectedIndex = i - 1;
                    }
                }
            
                var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                for (var j = 0; j < months.length; j++) {
                    var option = document.createElement("option");
                    option.value = j + 1;
                    option.text = months[j];
                    monthDropdown.add(option);
                    if (j + 1 === currentMonth) {
                        monthDropdown.selectedIndex = j;
                    }
                }
            
                for (var k = currentYear; k >= 1905; k--) {
                    var option = document.createElement("option");
                    option.value = k;
                    option.text = k;
                    yearDropdown.add(option);
                    if (k === currentYear) {
                        yearDropdown.selectedIndex = currentYear - 1905;
                    }
                }
            }
            
            function displayImage() {
                var fileInput = document.getElementById('fileInput');
                var image = document.getElementsByClassName('image')[0];
                var cropper;
            
                fileInput.addEventListener('change', function () {
                    var file = fileInput.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            image.src = e.target.result;
                            if (cropper) {
                                cropper.destroy();
                            }
                            if (image.width > 200 || image.height > 200) {
                                cropper = new Cropper(image, {
                                    aspectRatio: 1,
                                    viewMode: 1,
                                    dragMode: 'move',
                                    crop: function (event) {
                                    }
                                });
                                cropper.crop();
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            document.addEventListener("DOMContentLoaded", function () {
                populateDropdowns();
                displayImage();
            });
            function displayMessage(message, messageType) {
                var messageContainer = document.getElementById('message-container');
                messageContainer.innerHTML = '<div class="' + messageType + '">' + message + '</div>';
            }
            </script>

    </head>
    <body>
    
        <div id="message-container" class="message-container"></div>
        <form method="post">
            <div class="container">
                <div class="left-side">
                    <h2>Upload Your Image</h2>
                    <div class="upload-btn-wrapper">
                        <img class="image" name ="image" src="./images/profile.png" alt="">
                        <button class="btn choose" onclick="document.getElementById('fileInput').click()">Add Your Photo</button>
                        <input type="file" id="fileInput" accept="image/*">
                    </div>
                </div>
                <div class="right-side">
                    <h2>Sign Up</h2>
                  
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <div class="date">
                            <label for="dob">Date of Birth:</label>
                        </div>
                        <div class="dob-container">
                            <select name="day" required></select>
                            <select name="month" required></select>
                            <select name="year" required></select>
                        </div>
                        <div class="gender-container">
                            <label>Gender:</label>
                            <input type="radio" name="gender" value="male" id="male" required> 
                            <label for="male">Male</label>
                            <input type="radio" name="gender" value="female" id="female" required> 
                            <label for="female">Female</label>
                            <input type="radio" name="gender" value="custom" id="custom" required> 
                            <label for="custom">Custom</label>
                        </div>
                        <button type="submit" name="submit" class="submit-btn">Create Account</button>
                    </form>
                    <h3>If you already have an account</h3>
                    <div class="login-link">
                        <a href="login.html">Login</a></p>
                    </div>
                </div>
            </div>
    </body>
</html>

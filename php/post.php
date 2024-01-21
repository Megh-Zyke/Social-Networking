<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include 'connection.php';

    $postText = isset($_POST['postText']) ? $_POST['postText'] : '';

    $targetDir = 'uploads/';
    if (!file_exists($targetDir) && !is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $mediaFileName = '';
    if ($_FILES['mediaButton']['error'] == 0) {
        $targetDir = 'uploads/';
        $mediaFileName = $targetDir . basename($_FILES['mediaButton']['name']);
        move_uploaded_file($_FILES['mediaButton']['tmp_name'], $mediaFileName);
    }

    
    if ($postText === '' && $mediaFileName === '') {
        echo "Error: Both post content and image cannot be null.";
        exit();
    }

    $sql = "INSERT INTO posts (post_content, post_image_path, post_date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);


    $stmt->bind_param("ss", $postText, $mediaFileName);

    if ($stmt->execute()) {
        header("Location: user.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }   

    $stmt->close();
    $conn->close();
}
?>  

   
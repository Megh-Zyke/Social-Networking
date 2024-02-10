<?php
if(isset($_POST['postId']) && !empty($_POST['postId'])) {
    include 'connection.php';
    $postId = mysqli_real_escape_string($conn, $_POST['postId']);

    $sql = "DELETE FROM posts WHERE post_id = '$postId'";

    if(mysqli_query($conn, $sql)) {
        http_response_code(200);
        header("Location: ../user.php");
        exit;
        
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Error: Unable to delete post."));
    }

    mysqli_close($conn);
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Bad request: postId is missing or empty."));
}
?>

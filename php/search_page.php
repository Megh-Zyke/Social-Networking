<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];


// Fetch user bio from the database
$select_bio = $conn->prepare("SELECT bio , profile_image_url , first_name , last_name  FROM users WHERE user_id = ?");
$select_bio->bind_param("i", $current_user_id);
$select_bio->execute();
$select_bio->bind_result($default_bio, $profile, $first_name, $last_name);
$select_bio->fetch();
$select_bio->close();

$friends_number = $conn->prepare("SELECT users.friends , COUNT(posts.post_id)  
                                FROM users
                                JOIN posts ON users.user_id = ? AND posts.user_id = ?");
$friends_number->bind_param("ii", $current_user_id, $current_user_id);
$friends_number->execute();
$friends_number->bind_result($friends_count_list, $posts_count);
$friends_number->fetch();
$friends_number->close();
$decoded_friends = json_decode($friends_count_list, true);
if ($decoded_friends == null) {
    $decoded_friends = array();
}
$friends_count = count($decoded_friends);

include 'header.php';
include 'navbar.php';
?>

<div class="search-grid">
    <div class="info">
        <?php
        include 'home_left.php';
        ?>
    </div>

    <div class="users_search">
    </div>
</div>


<?php
include 'connection.php';

// Function to extract numeric part from a string
function extract_number($string) {
    preg_match_all('/\d+/', $string, $matches);
    return isset($matches[0]) ? (int)$matches[0][0] : null;
}

// Get the post ID from the URL parameter
if(isset($_GET['post_id'])) {
    $post_id_series = $_GET['post_id']; // Series of characters
    $post_id = extract_number($post_id_series); // Extract numeric part from the series

    // Fetch post details
    $get_post_details = $conn->prepare("SELECT p.*, u.first_name, u.last_name, u.profile_image_url 
        FROM posts p 
        JOIN users u ON p.user_id = u.user_id 
        WHERE p.post_id = ?");
    $get_post_details->bind_param("i", $post_id);
    $get_post_details->execute();
    $post_details = $get_post_details->get_result()->fetch_assoc();

    // Fetch comments for the post
    $get_comments = $conn->prepare("SELECT c.*, u.first_name, u.last_name, u.profile_image_url 
        FROM comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.post_id = ?
        ORDER BY c.comment_date DESC");
    $get_comments->bind_param("i", $post_id);
    $get_comments->execute();
    $comments = $get_comments->get_result()->fetch_all(MYSQLI_ASSOC);

    // Close prepared statements
    $get_post_details->close();
    $get_comments->close();
} else {
    // Handle case where post ID is not provided
    echo "Error: Post ID not provided.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="post_detailss.css">
</head>
<body>
    <div class="post_details_container">
        <!-- Display post details -->
        <div class="post_details">
            <!-- Display user profile picture and name -->
            <div class="user_info">
                <img src="<?php echo $post_details['profile_image_url']; ?>" alt="Profile Picture" class="profile_picture">
                <p class="user_name"><?php echo $post_details['first_name'] . ' ' . $post_details['last_name']; ?></p>
            </div>
            <!-- Display post date -->
            <p class="post_date"><?php echo $post_details['post_date']; ?></p>
            <!-- Display post content -->
            <p class="post_content"><?php echo $post_details['post_content']; ?></p>
            <!-- Display post image -->
            <?php if (!empty($post_details['post_image_path'])) : ?>
                <img src="<?php echo $post_details['post_image_path']; ?>" alt="Post Image" class="post_image">
            <?php endif; ?>
        </div>

        <!-- Display comments -->
        <div class="comments_container">
            <?php foreach ($comments as $comment) : ?>
                <div class="comment">
                    <!-- Display commenter profile picture and name -->
                    <div class="user_info">
                        <img src="<?php echo $comment['profile_image_url']; ?>" alt="Profile Picture" class="profile_picture">
                        <p class="commenter_name"><?php echo $comment['first_name'] . ' ' . $comment['last_name']; ?></p>
                    </div>
                    <!-- Display comment date -->
                    <p class="comment_date"><?php echo $comment['comment_date']; ?></p>
                    <!-- Display comment content -->
                    <p class="comment_content"><?php echo $comment['comment_text']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

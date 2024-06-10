<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
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

<div class="home-grid">
    <div class="info">
        <?php
        include 'home_left.php';
        ?>
    </div>

    <div class="posts">

        <?php
        include 'add_post_page.php';
        ?>
        <?php
        $friends = $conn->prepare("SELECT friends FROM users WHERE user_id = ? ");
        $friends->bind_param("i", $current_user_id);
        $friends->execute();
        $friends->bind_result($friends_list);
        $friends->fetch();

        $friends_array = json_decode($friends_list, true);

        if ($friends_array == null) {
            $friends_array = array();
        }
        $friends->close();
        
        $friends_array[] = $current_user_id;
        foreach ($friends_array as $friend) {
            $get_friend = $conn->prepare("SELECT * FROM posts JOIN users on (users.user_id = ?  and posts.user_id = ?)");
            $get_friend->bind_param("ii", $friend,$friend);
            $get_friend->execute();
            $result = $get_friend->get_result();
            

            while($row = $result->fetch_assoc()){

            if($row == null) {
                continue;
            }

            ?>

        <!-- post template start -->
        <div class="post_template">
            <div class="user_post_info">

                <div class="user_post_details">
                    <div class="user_post_profile">
                        <img src=<?php echo $row['profile_image_url']; ?> alt="" class="user_post_profile_img">
                    </div>

                    <div>
                        <div class="user_post_username">
                            <p class="user_post_username_text">
                                <?php echo $row['first_name'] . ' ' . $row['last_name'] ?>
                            </p>
                        </div>

                        <div class="date">
                            <?php
                                $postDate = new DateTime($row['post_date']);
                                echo $postDate->format('d-m-Y');
                                ?>
                        </div>

                    </div>

                </div>

            </div>


            <div class="post_content">
				 <a href="post_details.php?post_id=cE?<?php echo $row['post_id']; ?>fF?">
                <img src="<?php echo $row['post_image_path']; ?>" alt="" class="post_content_img">
				</a>
            </div>

            <?php if (!empty($row['post_content'])) { ?>


            <div class="post_description">
                <p class="post_description_text">
                    <?php echo $row['post_content']; ?>
                </p>
            </div>
            <?php } ?>



            <div class="post_buttons">

                <?php

                    $likes_array = $row['likes'];
                    $likes_array = json_decode($likes_array, true);
                    $like_count = count($likes_array);
                    if ($likes_array == null) {
                        $likes_array = array();
                    }

                    $color = "black";
                    $user_index = array_search($current_user_id, $likes_array);

                    if ($user_index !== false) {
                        $color = "red";
                    } else {
                        $color = "black";
                    }
                    ?>

                <div class="like" onclick="addLike(<?php echo $row['post_id'] ?>)">
                    <button> <i class="fa-solid fa-heart " id=<?php echo 'like' . $row['post_id'] ?>
                            style="color :  <?php echo $color ?> "></i> </button>
                    <span id=<?php echo 'like_count' . $row['post_id'] ?>><?php echo $like_count ?></span>
                </div>

                <div class="comment" onclick="toggleComment(<?php echo $row['post_id'] ?>)">
                    <button> <i class="fa-solid fa-comment"></i> </button>
                </div>

				<div class="share" data-post-id="<?php echo $row['post_id']; ?>">
    				<button> <i class="fa-solid fa-share"></i> </button>
</div>
            </div>

            <div class="comments" id="comments_<?php echo $row['post_id']; ?>" style="display:none;">
            <form id="commentForm_<?php echo $row['post_id']; ?>" class="comment_form">
                <input type="text" name="comment_text" placeholder="Write a comment">
                <button type="button" onclick="postComment(<?php echo $row['post_id']; ?>)">Post Comment</button>
            </form>
            <div id="commentList_<?php echo $row['post_id']; ?>" class="comment_list">
            </div>
    <?php
    $get_comments = $conn->prepare("SELECT c.*, u.first_name, u.last_name, u.profile_image_url 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.user_id 
    WHERE c.post_id = ?
    ORDER BY c.comment_date DESC");
    $get_comments->bind_param("i", $row['post_id']);
    $get_comments->execute();
    $result_comments = $get_comments->get_result();
    $comment_count = 0;

    while ($comment = $result_comments->fetch_assoc()) {
        $comment_count++;
        if ($comment_count <= 3) { // Display only the first three comments
            ?>
            <div class="comment_item">
                <div class="comment_user">
                    <img src="<?php echo $comment['profile_image_url']; ?>" alt="Profile Image"
                        class="profile_image_rounded">
                    <span><?php echo ucwords(strtolower($comment['first_name'] . ' ' . $comment['last_name'])); ?></span>
                </div>
                <div class="comment_text">
                    <p><?php echo $comment['comment_text']; ?></p>
                </div>
                <div class="comment_date">
                    <span><?php echo $comment['comment_date']; ?></span>
                </div>
            </div>
            <?php 
        } else { // If there are more than three comments, stop displaying and show the "See all comments" button
            break;
        }
    } 

    if ($comment_count > 3) { // Display the "See all comments" button if there are more than three comments
        ?>
        <div class="see_all_comments_button" data-post-id="<?php echo $row['post_id']; ?>">
            <button class="see_all_comments_button_btn" onclick="viewPostDetails(<?php echo $row['post_id']; ?>)">See all comments</button>
        </div>
    <?php } ?>
</div>
        </div>
        <?php } ?>
        <?php } ?>
    </div>

    <div class="info2">

        <div class="new_users_div">
            <p class="info2Heading">New Users</p>
            <div class="new_users">

                <?php
                //Fetch users from database
                $users = $conn->prepare("SELECT user_id, first_name, last_name, profile_image_url
                FROM users u
                LEFT JOIN friend_requests fr ON u.user_id = fr.recipient_id AND fr.status = 'pending'
                WHERE user_id != ?
                AND fr.request_id IS NULL -- Exclude users with pending friend requests
                ORDER BY user_id DESC 
                LIMIT 3;");
                $users->bind_param("i", $current_user_id);
                $users->execute();
                $users->bind_result($db_user_id, $fname, $lname, $profile_image_url);

                ?>
                <?php while ($users->fetch()) { ?>
                <div class="new_user_template">

                    <div class="new_user_profile">
                        <img src="<?php echo $profile_image_url; ?>" alt="" class="new_user_profile_img">
                    </div>

                    <div class="new_user_name">
                        <p class="new_user_name_text">
                            <?php echo $fname . " " . $lname ?>
                        </p>
                    </div>

                    <div class="new_user_add">
                        <button class="acceptButton" onclick="addFriend(<?php echo $db_user_id ?>)"> <i
                                id="<?php echo $db_user_id ?>"
                                class="fa-solid fa-plus <?php echo $db_user_id ?>"></i></button>
                    </div>

                </div>
                <?php } ?>

            </div>

        </div>



        <div class="requsets_div">

            <p class="info2Heading">Friend Requests</p>
            <div class="new_users">
                <?php


                $friend_requests = $conn->prepare("SELECT * FROM friend_requests JOIN users ON friend_requests.recipient_id = users.user_id WHERE sender_id = ? ");

                $friend_requests->bind_param("i", $current_user_id);
                $friend_requests->execute();
                $result = $friend_requests->get_result();

                while ($row = $result->fetch_assoc()) {
                    ?>

                <div class="new_user_template">
                    <div class="new_user_profile">
                        <img src="<?php echo $row['profile_image_url'] ?>" alt="" class="new_user_profile_img">
                    </div>

                    <div class="new_user_name">
                        <p class="new_user_name_text">
                            <?php echo $row['first_name'] . ' ' . $row['last_name'] ?>
                        </p>
                    </div>

                    <div class="friend_add" id=<?php echo "Friend" . $row['recipient_id'] ?>>
                        <div class="add">
                            <button class="acceptButton" onclick="confirmFriend( <?php echo $row['recipient_id'] ?>)">
                                <i class="fa-solid fa-check"></i></button>
                        </div>

                        <div class="cancel">
                            <button class="declineButton" onclick="deleteFriend(<?php echo $row['recipient_id'] ?>)"> <i
                                    class="fa-solid fa-cancel"></i> </button>
                        </div>
                    </div>

                    <div class="accepted" id=<?php echo "accepted" . $row['recipient_id'] ?>>
                        <button class="acceted_friend"> Request accepted.
                        </button>
                    </div>

                    <div class="not_accepted" id=<?php echo "reject" . $row['recipient_id'] ?>>
                        <button class="reject_friend"> Request canceled.
                        </button>
                    </div>

                </div>

                <?php } ?>

            </div>

        </div>


        <div class="friends_div">
            <p class="info2Heading">Friends</p>
            <div class="new_users">
                <?php

                $friends = $conn->prepare("SELECT friends FROM users WHERE user_id = ? ");
                $friends->bind_param("i", $current_user_id);
                $friends->execute();
                $friends->bind_result($friends_list);
                $friends->fetch();

                $friends_array = json_decode($friends_list, true);

                if ($friends_array == null) {
                    $friends_array = array();
                }
                $friends->close();

                foreach ($friends_array as $friend) {
                    $get_friend = $conn->prepare("SELECT * FROM users WHERE user_id = ? ");
                    $get_friend->bind_param("i", $friend);
                    $get_friend->execute();
                    $result = $get_friend->get_result();
                    $row = $result->fetch_assoc();

                    ?>

                <div class="new_user_template">
                    <div class="new_user_profile">
                        <img src="<?php echo $row['profile_image_url']; ?>" alt="" class="new_user_profile_img">
                    </div>

                    <div class="new_user_name">
                        <p class="new_user_name_text">
                            <?php echo $row['first_name'] . ' ' . $row['last_name'] ?>
                        </p>
                    </div>

                </div>
                <?php } ?>

            </div>

        </div>


        <!-- done -->

    </div>
</div><div class="overlay" id="overlay" style = "position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); 
        z-index: 999;
        display: none;"></div>

<div class="share_menu" id="shareMenu" style=" position: fixed; 
    top: 50%; 
    left: 50%; 
    transform: translate(-50%, -50%); 
    width: 300px; 
    height: 150px; 
    background-color: #ffffff; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    padding: 20px; 
    z-index: 1000; 
    display: none;
    transition: all 0.3s ease-in-out;">
	
    <span class="close_icon" style="position: absolute; top: 5px; right: 5px; cursor: pointer;">&#10006;</span>
	
    <div style="margin: 5%">How do you want to share this post?</div>
	
    <div class="share_icons" style="display: flex; justify-content: space-around; align-items: center; margin-top: 10px;">
        <div class="whatsapp_logo" id="whatsappLogo" style="font-size: 40px; color: #25d366; cursor: pointer;">
            <i class="fab fa-whatsapp"></i>
        </div>
		
<div class="twitter_logo" id="twitterButton" style="font-size: 40px; cursor: pointer;">
    <i class="fa-brands fa-x-twitter"></i>

</div>
		
        <div class="document_logo" id="copyLinkLogo" style="font-size: 40px; color: #007bff; cursor: pointer;">
            <i class="far fa-file-alt"></i>
           
        </div>
    </div>
</div>

<?php include "mobile_tabbar.php"; ?>

<script>
let currentPostId = null;

document.getElementById('mediaButton').addEventListener('change', previewImage);

function previewImage() {
    const imageInput = document.getElementById('mediaButton');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput.files && imageInput.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        };

        reader.readAsDataURL(imageInput.files[0]);
    }
}

function toggleComment(postId) {
    var commentsDiv = document.getElementById('comments_' + postId);
    if (commentsDiv.style.display === 'none') {
        commentsDiv.style.display = 'block';
    } else {
        commentsDiv.style.display = 'none';
    }
}

function showAllComments(postId) {
    // Select all comments for the specified post
    var comments = document.querySelectorAll('.comment_item[data-post-id="' + postId + '"]');

    // Loop through all comments and toggle their display
    comments.forEach(function(comment) {
        comment.style.display = 'block';
    });

    // Hide the "See all comments" button
    var seeAllCommentsButton = document.querySelector('.see_all_comments_button[data-post-id="' + postId + '"]');
    seeAllCommentsButton.style.display = 'none';
}

function addLike(postId) {
    console.log("Adding like to post with ID: " + postId);
    var xhr = new XMLHttpRequest();

    var likeBtn = document.getElementById("like" + postId);
    var color = likeBtn.style.color;

    // Determine if the post is being liked or unliked
    var isLiked = color === "red";

    // Update the color of the like button
    likeBtn.style.color = isLiked ? "black" : "red";

    xhr.open("POST", "add_like.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Parse the response to get the updated like count
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    var likeCountElement = document.getElementById("like_count" + postId);
                    // Ensure like count doesn't go below zero
                    var newLikeCount = Math.max(response.likeCount, 0);
                    likeCountElement.textContent = newLikeCount;
                } else {
                    console.error("Failed to add like: " + response.message);
                }
            } else {
                console.error("Failed to add like: " + xhr.status);
            }
        }
    };

    var data = JSON.stringify({ "post_id": postId });
    xhr.send(data);
}

function postComment(postId) {
    var commentForm = document.getElementById("commentForm_" + postId);
    var commentText = commentForm.querySelector("input[name='comment_text']").value.trim();

    if (commentText === "") {
        alert("Please enter a comment");
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_comment.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Comment added successfully
                var commentList = document.getElementById("commentList_" + postId);
                commentList.innerHTML += xhr.responseText; // Append the new comment
                commentForm.reset(); // Reset the form
                
                // Check if there are more than three comments
                var comments = commentList.querySelectorAll(".comment_item");
                if (comments.length > 3) {
                    // Remove the last comment
                    commentList.removeChild(commentList.lastElementChild);
                }
            } else {
                alert("Error adding comment: " + xhr.responseText);
            }
        }
    };
    xhr.send("post_id=" + postId + "&comment_text=" + encodeURIComponent(commentText));
}

function viewPostDetails(postId) {
    window.location.href = 'post_details.php?post_id=' + postId;
}

function showShareMenu(postId) {
    currentPostId = postId;
    document.getElementById('shareMenu').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function hideShareMenu() {
    document.getElementById('shareMenu').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

document.querySelector('.close_icon').addEventListener('click', hideShareMenu);

document.querySelectorAll('.share').forEach(function(button) {
    button.addEventListener('click', function() {
        showShareMenu(button.getAttribute('data-post-id'));
    });
});

document.getElementById('copyLinkLogo').addEventListener('click', function() {
    var link = window.location.origin + '/post_details.php?post_id=?njz' + currentPostId+"?Lkkj";
    navigator.clipboard.writeText(link).then(function() {
        alert('Link copied to clipboard');
    }, function(err) {
        console.error('Failed to copy link: ', err);
    });
    hideShareMenu();
});

document.getElementById('whatsappLogo').addEventListener('click', function() {
    var link = window.location.origin + '/post_details.php?post_id=Sn?uiW' + currentPostId +'#Kibl';
	   var message = "Hey! Check out this interesting post I found: \n Here's the link: " + encodeURIComponent(link) + " \n\nLet me know what you think!";
    var whatsappUrl = 'https://wa.me/?text= ' + message; 
    window.open(whatsappUrl, '_blank');
    hideShareMenu();
});
	
	
	document.getElementById('twitterButton').addEventListener('click', function() {
    // URL of the post you want to share
    var postUrl =  window.location.origin + '/post_details.php?post_id=Sn?uiW' + currentPostId +'#Kibl';

    // Tweet text
    var tweetText = 'Check out this post: ' + postUrl;

    // Construct Twitter share URL
    var twitterUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(tweetText);

    // Open a new window with the Twitter compose tweet page
    window.open(twitterUrl, '_blank');
});
</script>

<script src="JS/userPage.js"></script>

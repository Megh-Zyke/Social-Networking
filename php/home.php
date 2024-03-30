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

include 'header.php';
include 'navbar.php';
?>

<div class="home-grid">
    <div class="info">
        <?php
        include 'right_grid.php';
        ?>
    </div>

    <div class="posts">

        <?php
        include 'add_post_page.php';
        ?>


        <?php
        $sql = "SELECT * 
                    FROM posts
                    JOIN users ON posts.user_id = users.user_id
                    ORDER BY posts.post_date DESC";


        $result = $conn->query($sql);

        $posts = array();

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        foreach ($posts as $row) {
            $postId = isset($row['post_id']) ? $row['post_id'] : null;
            ?>
            <!-- post template start -->
            <div class="post_template">

                <div class="user_post_info">

                    <div class="user_post_details">
                        <div class="user_post_profile">
                            <img src=<?php echo $row['profile_image_url']; ?> alt="" class="user_post_profile_img">
                        </div>

                        <div class="user_post_username">
                            <p class="user_post_username_text">
                                <?php echo $row['first_name'] . ' ' . $row['last_name'] ?>
                            </p>
                        </div>

                    </div>

                    <div class="date">
                        <?php
                        $postDate = new DateTime($row['post_date']);
                        echo $postDate->format('d-m-Y');
                        ?>
                    </div>

                </div>


                <div class="post_content">
                    <?php
                    if (!empty($row['post_image_path'])) {
                        ?>


                        <img src="<?php echo $row['post_image_path']; ?>" alt="" class="post_content_img">
                    </div>

                    <?php
                    }
                    ?>
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
                    </div>

                    <div class="comment">
                        <button> <i class="fa-solid fa-comment"></i> </button>
                    </div>

                    <div class="share">
                        <button> <i class="fa-solid fa-share"></i> </button>
                    </div>
                </div>
            </div>

        <?php } ?>
        <!-- post template done -->



    </div>

    <div class="info2">

        <div class="new_users_div">
            <p class="info2Heading">New Users</p>
            <div class="new_users">

                <?php
                //Fetch users from database
                $users = $conn->prepare("SELECT user_id , first_name , last_name , profile_image_url FROM users WHERE user_id != ? ORDER BY user_id DESC LIMIT 3 ");
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
                                <button class="declineButton"  onclick="deleteFriend(<?php echo $row['recipient_id'] ?>)"> <i class="fa-solid fa-cancel"></i> </button>
                            </div>
                        </div>

                        <div class="accepted" id=<?php echo "accepted" . $row['recipient_id'] ?>>
                            <button class="acceted_friend"> Request accepted.
                            </button>
                        </div>

                        <div class="not_accepted"  id=<?php echo "reject" . $row['recipient_id'] ?>>
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
</div>

<script>
    document.getElementById('mediaButton').addEventListener('change', previewImage);

    function previewImage() {
        const imageInput = document.getElementById('mediaButton');
        const imagePreview = document.getElementById('imagePreview');

        if (imageInput.files && imageInput.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };

            reader.readAsDataURL(imageInput.files[0]);
        }
    }

</script>
<script src="JS/userPage.js"></script>
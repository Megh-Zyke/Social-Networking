<div class="user_info">

    <div class="userInfo">
        <div class="user_image">
            <img src=<?php echo $profile ?> alt="UserImage">
        </div>

        <div class="user_name">
            <p>
                <?php echo $first_name . " " . $last_name ?>
            </p>
        </div>

    </div>

</div>

<div class="user_stats">
    <div class="user_posts">
        <div class="icon">
            <button class="icon_button"> <i class="fa-solid fa-user-plus"></i></button>

        </div>

        <div class="user_posts_count">
            <?php echo $friends_count ?>
        </div>
    </div>

    <div class="user_friends">
        <div class="icon">
            <button class="icon_button">
                <i class="fa-regular fa-file-image"></i>
            </button>

        </div>
        <div class="user_friends_count">
        <?php echo $posts_count ?>
        </div>
    </div>


</div>


<div class="bio">
    <div>
        <p class="bioTitle">
        <h2>Bio</h2>
        </p>
    </div>
    <div>
        <textarea readonly rows=4 class="user_bio"><?php echo $default_bio ?></textarea>

    </div>

    <div>
        <button class="bioEditButton" onclick="editBio()"> Edit Bio </button>
    </div>
</div>

<div class="connections">

    <div class="add_friend">
        <button class="icon_button">
            <i class="fa-solid fa-user-plus"></i>
        </button>
    </div>

    <div class="message_user">
        <button class="icon_button">
            <i class="fa-solid fa-comment"></i>
        </button>
    </div>

    <div class="user_phone">
        <button class="icon_button">
            <i class="fa-solid fa-phone"></i>
        </button>
    </div>
</div>


<div class="add_post">
    <button class="bioEditButton" onclick="openPosts()">Add Post</button>
</div>
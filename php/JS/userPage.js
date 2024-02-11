
        function openPosts() {
            console.log("open");
            document.querySelector(".addPostsPage").style.display = "block";
        }
    
        function closePage() {
            document.querySelector(".addPostsPage").style.display = "none";
        }

        function confirmDelete() {
        var result = confirm("Are you sure you want to delete this post?");
        return result;
    }

    function editBio() {
            var bioElement = document.querySelector(".bio textarea");
            bioElement.removeAttribute("readonly");
            bioElement.focus();
            bioElement.selectionStart = bioElement.selectionEnd = bioElement.value.length;
            var bioButton = document.querySelector(".bioEditButton");
            bioButton.textContent = "Save Bio";
            bioButton.onclick = saveBio;
        }

    function saveBio() {
            var bioElement = document.querySelector(".bio textarea");
            var newBio = bioElement.value.trim();
            bioElement.setAttribute("readonly", "readonly");

            var bioButton = document.querySelector(".bioEditButton");
            bioButton.textContent = "Edit Bio";
            bioButton.onclick = editBio;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_bio.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    
                    console.log("Bio updated successfully");
                } else {
                   
                    console.error("Error updating bio: " + xhr.responseText);
                }
            }
        };
    xhr.send("bio=" + encodeURIComponent(newBio)); // Send the updated bio data
    }

    function editPost(postId) {
        console.log("Editing post with ID: " + postId);
        var postTextElement = document.getElementById("post" + postId);
        console.log();
        var text = postTextElement.textContent;
        if (text === null || text === "") {
            var updatedPostText = prompt("Enter the updated post text:" );
        }
        else {
            var updatedPostText = prompt("Enter the updated post text:", text);
        }
        
       
            if (updatedPostText !== null) {
            updatePost(postId, updatedPostText);
        }
    }

    function updatePost(postId, updatedPostText) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log("Post updated successfully");
                    // Update the post text on the page
                    document.querySelector("#postText_" + postId).textContent = updatedPostText;
                } else {
                    console.error("Error updating post: " + xhr.responseText);
                }
            }
            };
            xhr.send("postId=" + postId + "&postText=" + encodeURIComponent(updatedPostText));
    }


    function addLike(postId) {
        console.log("Adding like to post with ID: " + postId);
        var xhr = new XMLHttpRequest();

        var likeBtn = document.getElementById("like" + postId);
        var color = likeBtn.style.color;

        if (color == "red"){
            likeBtn.style.color = "black";
        } else {
            likeBtn.style.color = "red";
        }

        xhr.open("POST", "add_like.php", true);
        
        xhr.setRequestHeader("Content-Type", "application/json");
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                } else {
                    console.error("Failed to add like: " + xhr.status);
                }
            }
        };
        
        var data = JSON.stringify({ "post_id": postId });
        console.log(data);
        xhr.send(data);
    }


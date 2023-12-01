<?php
// Create connection
$conn = mysqli_connect("localhost", "root", "", "psi");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_errno();
}

// Start or resume the session
session_start();

// Check if the user is logged in
if (isset($_SESSION['USERID'])) {
    // Retrieve the user ID from the session
    $userid = $_SESSION['USERID'];

    // Fetch the username and profile image from the database based on the USERID
    $userQuery = "SELECT USERNAME, IMAGE AS USER_IMAGE FROM USERS WHERE USERID = '$userid'";
    $userResult = $conn->query($userQuery);

    if ($userResult->num_rows == 1) {
        $userRow = $userResult->fetch_assoc();
        $username = $userRow['USERNAME'];
        $userImage = $userRow['USER_IMAGE'];
    }

    // Fetch posts with or without the filtered tag from both IDEAS and PROBLEMS tables
    $postQuery = "(SELECT 'idea' as post_type, IDEAID, IDEAS.TITLE, IDEAS.TAGID, TEXT, IMAGE, IDEAS.USERID, VOTESCORE
                    FROM IDEAS
                    INNER JOIN TAGS ON IDEAS.TAGID = TAGS.TAGID
                    WHERE IDEAS.USERID = '$userid')
                    UNION
                    (SELECT 'problem' as post_type, PROBLEMID, PROBLEMS.TITLE, PROBLEMS.TAGID, TEXT, IMAGE, PROBLEMS.USERID, VOTESCORE
                    FROM PROBLEMS
                    INNER JOIN TAGS ON PROBLEMS.TAGID = TAGS.TAGID
                    WHERE PROBLEMS.USERID = '$userid')
                    ORDER BY VOTESCORE DESC";

    $postResult = $conn->query($postQuery);

    $posts = []; // Initialize an array to store posts

    if ($postResult->num_rows > 0) {
        while ($postRow = $postResult->fetch_assoc()) {
            $postType = $postRow["post_type"];
            $post_id  = ($postType == 'idea') ? $postRow["IDEAID"] : $postRow["PROBLEMID"];
            $postTitle = $postRow['TITLE'];
            $tagID = $postRow['TAGID'];
            $postContent = $postRow['TEXT'];
            $postImage = $postRow['IMAGE'];
            $userID = $postRow['USERID']; 
            $votescore = $postRow['VOTESCORE'];

            // Fetch tag information from TAGS table
            $tagQuery = "SELECT TAGS FROM tags WHERE TAGID = '$tagID'";
            $tagResult = $conn->query($tagQuery);

            if ($tagResult->num_rows == 1) {
                $tagRow = $tagResult->fetch_assoc();
                $tagName = $tagRow['TAGS'];

            } else {
                echo "Error fetching tag information.";
            }

            // Fetch Username information from USERS table (for the post)
            $userQuery = "SELECT USERNAME, IMAGE AS USER_IMAGE FROM users WHERE USERID = '$userID'";
            $userResult = $conn->query($userQuery);

            if ($userResult->num_rows == 1) {
                $userRow = $userResult->fetch_assoc();
                $userName = $userRow['USERNAME'];
                $userImage = $userRow['USER_IMAGE'];
            } else {
                echo "Error fetching User information.";
            }

            // Store each post in the $posts array
            $posts[] = [
                'postId' => $post_id,
                'postTitle' => $postTitle,
                'postContent' => $postContent,
                'tagName' => $tagName,
                'votescore' => $votescore,
                'userName' => $userName,
                'userId'=> $userid,
                'userImage' => $userImage
            ];
        }
    } else {
        echo "No posts found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="Styling/Profile-css.css">  
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>

<header>
    <div class="nav-bar">
        <div class="logo">
            <a href="iNOVAtion-html.php">
                <img src="Images/iNOVAtion Hub.png" alt="Logo" id="main-logo">
            </a>
        </div>
        <div class="header-links">
            <a href="#"><i class="uil uil-user"></i> Profile</a>
        </div>
        <div class="navigation">
            <div class="nav-items">
                <i class="uil uil-times nav-close-btn"></i>
                <a href="#"><i class="uil uil-home"></i> Home</a>
                <a href="#"><i class="uil uil-compass"></i> Explore</a>
                <a href="#"><i class="uil uil-info-circle"></i> About</a>
                <a href="#"><i class="uil uil-envelope"></i> Contact</a>
            </div>
        </div>
        <i class="uil uil-apps nav-menu-btn"></i>
    </div>
</header>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="edit-profile" id="editProfileBtn">
                <i class="uil uil-edit"></i> Edit Profile
            </div>
            <img src="<?php echo $userImage; ?>" alt="User Profile Picture" class="profile-picture" id="profilePicture">
            <div class="profile-info" id="profileInfo">
                <h1 class="username" id="username"><?php echo $username; ?></h1>
                <p class="description" id="description">User description goes here.</p>
            </div>
        </div>
        <div class="published-posts">
            <h2>Your Published Posts</h2>
            <!-- Check if the user has published posts -->
            <?php if (!empty($posts)) : ?>
                <?php foreach ($posts as $post) : ?>
    <div class="post">
        <div class="post-header">
            <div class="user-info">
                <img src="Images/persona.avif" alt="User Profile Picture">
                <span class="username"><?php echo $post['userName']; ?></span>
            </div>
            <a href="ViewPost-html.php?post_id=<?php echo $post['postId']; ?>">
                <h2 class="post-title"><?php echo $post['postTitle']; ?></h2>
                <p class="post-tag"><?php echo $post['tagName']; ?></p>
                <br>
                <p class="post-content"><?php echo $post['postContent']; ?></p>
            </a>
        </div>
        <div class="post-footer">
            <div class="post-actions">
                <form method="post" action="Php/vote.php">
                    <input type="hidden" name="post_id" value="<?php echo $post['postId']; ?>">
                    <button name="upvote" type="submit" class="upvote-button">
                        <i class="uil uil-arrow-up"></i>
                    </button>
                    <span class="post-stats"><?php echo $post['votescore']; ?></span>
                    <button name="downvote" type="submit" class="downvote-button">
                        <i class="uil uil-arrow-down"></i>
                    </button>
                </form>
                <a href="ViewPost-html.html#comments" class="post-link">
                    <button class="comment-button"><i class="uil uil-comment"></i></button>
                </a>
            </div>
            <?php if ($post['userId'] == $userid) : ?>
                <div class="edit-delete-buttons">
                    <!-- Edit button with icon -->
                    <a href="Create Post-html.php?edit_post_id=<?php echo $post['postId']; ?>">
                        <i class="uil uil-pen"></i> Edit
                    </a>
                    <!-- Delete button with icon -->
                    <button class="delete-button" onclick="confirmDelete(<?php echo $post['postId']; ?>)">
                        <i class="uil uil-trash-alt"></i> Delete
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else : ?>
        <p>No Posts Yet to Show!</p>
    <?php endif; ?>
        </div>
    </div>
</body>

<script type="text/javascript" src="Scripts/iNOVAtion-js.js"></script>

</body>
</html>
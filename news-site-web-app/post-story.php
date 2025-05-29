<?php
session_start();
if (isset($_POST['home-page'])) {
    header("Location: news-site.php");
}
if (isset($_POST['tech-filter'])) {
    header("Location: tech-page.php");
}   
if (isset($_POST['entmt-filter'])) {
    header("Location: entertainment-page.php");
}   
if (isset($_POST['sci-filter'])) {
    header("Location: science-page.php");
}   
if (isset($_POST['gmg-filter'])) {
    header("Location: gaming-page.php");
}   
if (isset($_POST['sprts-filter'])) {
    header("Location: sports-page.php");
}   
if (isset($_POST['hist-filter'])) {
    header("Location: history-page.php");
}   
if (isset($_POST['user-stories'])) {
    header("Location: user-stories.php");
}       
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post A Story</title>
    <link rel="stylesheet" type="text/css" href="news-site.css">
</head>
<body class="body-container">
    <!-- Website Header and Navigation Bar -->
    <div class="header-container">
        <h1 class="header-text">The Daily Instance</h1>
        <div class="nav-bar">
            <div class="logout-options">
            </div>
            <div class="nav-options">
                <form method="post">
                        <input class="page-header-options" type="submit" name="home-page" id="home-page" value="Home">
                        <input class="page-header-options" type="submit" name="tech-filter" id="tech-filter" value="Technology">
                        <input class="page-header-options" type="submit" name="entmt-filter" id="entmt-filter" value="Entertainment">
                        <input class="page-header-options" type="submit" name="sci-filter" id="sci-filter" value="Science">
                        <input class="page-header-options" type="submit" name="gmg-filter" id="gmg-filter" value="Gaming">
                        <input class="page-header-options" type="submit" name="sprts-filter" id="sprts-filter" value="Sports">
                        <input class="page-header-options" type="submit" name="hist-filter" id="hist-filter" value="History">
                        <input class="page-header-options" type="submit" name="user-stories" id="user-stories" value="My Stories">
                        <input class="page-header-options" type="submit" name="post-story" id="post-story" value="Post A Story">
                </form>
            </div>
            <!-- Logout Option -->
            <div class="logout-options">
                <?php
                if (!$_SESSION['logged-in'] and !$_SESSION['guest']) {
                    header("Location: login-page.php");
                }
                if ($_SESSION['guest']) {
                    echo "<form action='login-page.php' method='post'><input class='logout-btn' type='submit' name='go-login' value='Login'></form>";
                } else {
                    printf("<form method='POST'><label class='username'>%s</label><input class='logout-btn' type='submit' name='logout' value='Logout'></form>", $_SESSION['username']);
                    // If logout clicked, destroy session and redirect to login page
                    if (isset($_POST['logout'])) {
                        $_SESSION['logged-in'] = false;
                        session_destroy();
                        header("Location: login-page.php");
                        exit;
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Post-A-Story Form Section -->
    <div>
        <?php 
        // If guest, cannot post, asked to register or login
        if ($_SESSION['guest']) {
            echo "<h2 class='guest-message'>Uh oh! It appears that you are viewing this website as a guest. Only registered 
                    users can upload stories. To login, press the login button. To create an account, 
                    press the back register button</h2>\n";
            echo "<form action='registration-page.php'>\n";
            echo "\t<input class='logout-btn' type='submit' name='go-register' value='Register'>\n";
            echo "</form>\n";
            echo "<form action='login-page.php'>\n";
            echo "\t<input class='logout-btn' type='submit' name='go-login' value='Login'>\n";
            echo "</form>\n";
        } else { 
            echo "<h2 class='body-title'> To post a story, simply fill out the information below and click post.<br> Adding a link is optional.</h2>
                <div class='post-story-container'>
                <form class='edit-story-form' method='post'>
                <input type='hidden' name='token' value='" . $_SESSION['token'] . "'>
                <label for='story-title'>Title</label>
                <input type='text' minlength='10' maxlength='255' name='story-title' id='story-title' required>
                <label for='story-body'>Body</label>
                <textarea minlength='10' rows='5' cols='50' name='story-body' id='story-body' placeholder='Describe your story...' required></textarea>
                <label for='story-link'>Link</label>
                <input type='url' name='story-link' id='story-link'>
                <select class='genre-select' name='genre' id='genre'>
                    <option value='technology'>Technology</option>
                    <option value='entertainment'>Entertainment</option>
                    <option value='science'>Science</option>
                    <option value='history'>History</option>
                    <option value='gaming'>Gaming</option>
                    <option value='sports'>Sports</option>
                    <option value='other'>Other</option>
                </select>
                <input class='logout-btn' type='submit' name='post-a-story' id='post-a-story' value='Post Story'>
                </form>
                </div>";
        }
        ?>
        <!-- PHP inserting posted story into story database -->
        <?php 
        require "database.php";

        if (isset($_POST['story-title'], $_POST['story-body'], $_POST['genre'])) {
            $user_id = $_SESSION['user_id'];
            $title = htmlentities($_POST['story-title']);
            $body = htmlentities($_POST['story-body']);
            $link = htmlentities($_POST['story-link']);
            $genre = $_POST['genre'];

            if(!hash_equals($_SESSION['token'], $_POST['token'])){
                die("Request forgery detected");
            }

            $stmt = $mysqli->prepare("select title from user_stories where title=?");

            $stmt->bind_param('s', $title);

            $stmt->execute();

            $stmt->bind_result($existing_title);

            if (!($stmt->fetch())) {
                $add_story_query = $mysqli->prepare("insert into user_stories (user_id, title, body, link, genre) values (?, ?, ?, ?, ?)");

                $add_story_query->bind_param('sssss', $user_id, $title, $body, $link, $genre);
                $add_story_query->execute();
                $add_story_query->close();
                echo "<h3> Your story has been uploaded</h3>";
            } else {
                echo "<h3>Another story already has this title. Please enter a different title</h3>";
            }
            $stmt->close();
        }
        ?>
    </div>
</body>
</html>
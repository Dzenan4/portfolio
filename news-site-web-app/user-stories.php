<?php
session_start();
if (!$_SESSION['logged-in'] and !$_SESSION['guest']) {
    header("Location: login-page.php");
}
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
if (isset($_POST['post-story'])) {
    header("Location: post-story.php");
}     
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Stories</title>
    <link rel="stylesheet" type="text/css" href="news-site.css">
</head>
<body class="body-container">
    <!-- Website header and nav bar -->
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
            <div class="logout-options">
                <?php
                require 'database.php';
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
        <?php

        // Guest prompted to sign in or sign up to post and view stories
        if ($_SESSION['guest']) {
            echo "<h2 class='guest-message'>Uh oh! Looks like you are visiting as a guest\
                    Please login or create an account to be able to create stories and share them with other users.</h2>";
            echo "<form action='registration-page.php'>\n";
            echo "\t<input class='logout-btn' type='submit' name='go-register' value='Register'>\n";
            echo "</form>\n";
            echo "<form action='login-page.php'>\n";
            echo "\t<input class='logout-btn' type='submit' name='go-login' value='Login'>\n";
            echo "</form>\n";
        } else {
            // A list of stories created by the logged in user
            echo "<h2 class='body-title'> Here are your stories: </h2>";

            echo "<div>";
            echo "<ul class='story-body'>\n";

            // Functionality of existing editing story 
            // inserted ahead of other code so that it appears
            // the top of the page
            if (isset($_POST['edit-story-button'])) {
                $story_to_edit = htmlentities($_POST['edit-story-button']);
                $edit_story_id = $_POST['edit-story-id'];

                if(!hash_equals($_SESSION['token'], $_POST['token'])){
                    die("Request forgery detected");
                }

                $find_story_query = $mysqli->prepare("select title, body, link, genre from user_stories where title='" . $story_to_edit . "'");

                $find_story_query->execute();
                $find_story_query->bind_result($e_title, $e_body, $e_link, $e_genre);

                if (!($find_story_query->fetch())) {
                    echo "No story found";
                } else {
                    echo "<form class='edit-story-form' method='post'>";
                    echo "<input type='hidden' name='token' value='" . $_SESSION['token'] . "'>";
                    echo "<label for='story-title'>Title</label>";
                    printf("<input type='text' minlength='10' maxlength='255' name='story-title-edit' id='story-title' value='%s' required>\n", $e_title);
                    echo "<label for='story-body'>Body</label>";
                    printf("<textarea type='text' minlength='10' rows='5' cols='50' name='story-body-edit' id='story-body' required>%s</textarea>\n", $e_body);
                    echo "<label for='story-link'>Link</label>";
                    printf("<input type='url' name='story-link-edit' id='story-link-edit' placeholder=%s>\n", $e_link);
                    echo "<select class='genre-select' name='story-genre-edit' id='genre'>
                        <option value='technology'>Technology</option>
                        <option value='entertainment'>Entertainment</option>
                        <option value='science'>Science</option>
                        <option value='history'>History</option>
                        <option value='gaming'>Gaming</option>
                        <option value='sports'>Sports</option>
                        <option value='other'>Other</option>
                    </select>";
                    printf("<input type='hidden' name='edit-story-title' value='%s'>", $story_to_edit);
                    printf("\t\t\t\t<input type='hidden' name='edit-story-id' value='%u'>\n", $edit_story_id);
                    echo "<input class='logout-btn' type='submit' name='edit-story' id='edit-story' value='Confirm'>";
                    echo "</form>";
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";

                    $find_story_query->close();
                }
            }

            // Displaying all user stories
            $get_user_stories = $mysqli->prepare("select story_id, title, body, link, genre, publish_date from user_stories where user_id = " . $_SESSION['user_id'] . " order by publish_date desc");

            $get_user_stories->execute();

            $get_user_stories->bind_result($story_id, $title, $body, $link, $genre, $pub_date);

            $story_titles = [];
            
            while($get_user_stories->fetch()) {
                $story_titles[] = $title;
                echo "\t<li class='story-entry'>\n";
                echo "\t\t<div class='story-entry-container'>\n";
                echo "<div class='entry-header-container'>";
                        printf("<input class='story-entry-title' type='submit' name='view-story' value='%s'>\n", $title);
                        printf("<h5 class='entry-pub-date'>Published on %s by %s</h5>\n", $pub_date, $_SESSION['username']);
                echo "</div>";
                echo "<div class='entry-body'>";
                    printf("<p class='story-entry-body'>%s</p>\n", $body);
                    printf("<h3 class='story-entry-body'><strong>Genre:</strong> %s</h3>\n", $genre);
                    printf("<h3 class='story-entry-body'><strong>Link:</strong> %s</h3>\n", $link);
                echo "</div>\n";

                        printf("\t\t\t<form method='post'>\n");
                        printf("<input type='hidden' name='token' value='" . $_SESSION['token'] . "'>");
                        printf("\t\t\t\t<input type='hidden' name='edit-story-button' value='%s'>\n", $title);
                        printf("\t\t\t\t<input type='hidden' name='edit-story-id' value='%u'>\n", $story_id);
                        printf("\t\t\t\t<input class='logout-btn' type='submit' value='Edit'>");
                        printf("\t\t\t</form>");

                        printf("\t\t\t<form method='post'>\n");
                        printf("<input type='hidden' name='token' value='" . $_SESSION['token'] . "'>");
                        printf("\t\t\t\t<input type='hidden' name='delete-story-button' value='%s'>\n", $title);
                        printf("\t\t\t\t<input class='logout-btn' type='submit' value='Delete'>");
                        printf("\t\t\t</form>");
                        

                echo "\t\t</div>\n";
                echo "\t</li>";
            }
            echo "</ul>\n";
            echo "</div>\n";
            $get_user_stories->close();
            
            // Functionality of deleting user story
            if (isset($_POST['delete-story-button'])) {
                $story_to_delete = $_POST['delete-story-button'];
                if(!hash_equals($_SESSION['token'], $_POST['token'])){
                    die("Request forgery detected");
                }

                $delete_story_query = $mysqli->prepare("delete from user_stories where title='" . $story_to_delete ."'");
                $delete_story_query->execute();
                $delete_story_query->close();

                header("Location: " . $_SERVER['PHP_SELF']);
            }

            // Continued functionality of editing user story
            if (isset($_POST['story-title-edit'], $_POST['story-body-edit'], $_POST['story-genre-edit'])) {
                $edit_story_id = $_POST['edit-story-id'];
                $story_to_edit = htmlentities($_POST['edit-story-title']);
                $user_id = $_SESSION['user_id'];
                $title = htmlentities($_POST['story-title-edit']);
                $body = htmlentities($_POST['story-body-edit']);
                $link = htmlentities($_POST['story-link-edit']);
                $genre = $_POST['story-genre-edit'];

                if(!hash_equals($_SESSION['token'], $_POST['token'])){
                    die("Request forgery detected");
                }
        
                $stmt = $mysqli->prepare("select title from user_stories where title=? and story_id != " . $edit_story_id);
    
                $stmt->bind_param('s', $title);
        
                $stmt->execute();
    
                $stmt->bind_result($existing_title);
        
                if (!($stmt->fetch())) {
                    $edit_story_query = $mysqli->prepare("update user_stories set title = '" . $title . "', body = '" . $body . "', link = '" . $link . "', genre = '" . $genre . "' where title='" . $story_to_edit."'");
                    $edit_story_query->execute();
                    $edit_story_query->close();
                    echo "<h3> Your story has been updated</h3>";
                    header("Location: " . $_SERVER['PHP_SELF']);
                } else {
                    echo "<h3>Another story already has this title. Please enter a different title</h3>";
                }
                $stmt->close();
            }
        }
        ?>
</body>
</html>
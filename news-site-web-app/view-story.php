<?php
session_start();
require 'database.php';
// Navigation redirects
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
if (isset($_POST['user-stories'])) {
    header("Location: user-stories.php");
} 
if (isset($_POST['post-story'])) {
    header("Location: post-story.php");
}       

// Comment submission functionality
if (isset($_POST['submit-comment'])) {
    $story_id = $_POST['story-for-comment'];
    $user_id = $_SESSION['user_id'];
    $comment = htmlentities($_POST['comment-text']);

    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }

    if (!(strlen($comment) === 0)) {
        $stmt = $mysqli->prepare("insert into comments (user_id, story_id, comment_text) values (?, ?, ?)");

        $stmt->bind_param('iis', $user_id, $story_id, $comment);

        $stmt->execute();

        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
    }
}

// Comment cancellation functionality
if (isset($_POST['cancel-comment'])) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Comment deletion functionality
if (isset($_POST['delete-own-comment'])) {
    $delete_comment_id = $_POST['modify-comment-id'];

    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }

    $delete_comment_query = $mysqli->prepare("delete from comments where comment_id=" . $delete_comment_id);
    $delete_comment_query->execute();
    $delete_comment_query->close();

    header("Location: " . $_SERVER['PHP_SELF']);
}

// Comment edit functionality
if (isset($_POST['submit-comment-changes'])) {
    $new_comment_text = htmlentities($_POST['comment-edit']);
    $comment_id = $_POST['modify-comment-id'];

    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }

    $edit_comment_query = $mysqli->prepare("update comments set comment_text = '" . $new_comment_text . "' where comment_id = " . $comment_id);
    $edit_comment_query->execute();
    $edit_comment_query->close();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Comment upvote functionality
if (isset($_POST['upvote-comment'])) {
    $comment_id = $_POST['comment-of-vote'];

    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }

    if ($_SESSION['guest']) {
        echo "<h2>You must create an account to vote comments</h2>";
        echo "<form action='registration-page.php'>\n";
                echo "\t<input class='logout-btn' type='submit' name='go-register' value='Register'>\n";
                echo "</form>\n";
                echo "<form action='login-page.php'>\n";
                echo "\t<input class='logout-btn' type='submit' name='go-login' value='Login'>\n";
                echo "</form>\n";
    } else {
        $check_vote = $mysqli->prepare("select id from user_upvotes where comment_id=" . $comment_id . " and user_id=" . $_SESSION['user_id']);
        $check_vote->execute();
        $check_vote->bind_result($vote_id);
        $exists = $check_vote->fetch();
        $check_vote->close();

        if (!($exists)) {
            $insert_vote = $mysqli->prepare("insert into user_upvotes (comment_id, user_id) values (" . $comment_id . ", " . $_SESSION['user_id'] . ")");         
            $insert_vote->execute();
            $insert_vote->close();

            $update_vote = $mysqli->prepare("update comments set upvote_value = upvote_value + 1 where comment_id=" . $comment_id);
            $update_vote->execute();
            $update_vote->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);

    }
}

// Comment downvote functionality
if (isset($_POST['downvote-comment'])) {
    $comment_id = $_POST['comment-of-vote'];

    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }

    $check_vote = $mysqli->prepare("select count(*) from user_upvotes where comment_id=" . $comment_id . " and user_id=" . $_SESSION['user_id']);
    $check_vote->execute();
    $check_vote->bind_result($vote_id);
    $check_vote->fetch();
    $check_vote->close();
    if ($vote_id === 1) {
        $insert_vote = $mysqli->prepare("delete from  user_upvotes where comment_id=" . $comment_id . " and user_id=" . $_SESSION['user_id']);         
        $insert_vote->execute();
        $insert_vote->close();

        $update_vote = $mysqli->prepare("update comments set upvote_value = upvote_value - 1 where comment_id=" . $comment_id);
        $update_vote->execute();
        $update_vote->close();
    } 
    header("Location: " . $_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View A Story</title>
    <link rel="stylesheet" type="text/css" href="news-site.css">
</head>
<body class="body-container">
     <!-- Website Header and Navigation Bar-->
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
    <div>
        <!-- Display of selected story -->
        <?php

        $story_to_view = $_SESSION['story-to-view'];
        $current_user_id;

        if (!($_SESSION['guest'])) {
            $current_user_id = $_SESSION['user_id'];

        }

        $story_details = $mysqli->prepare("select title, body, link, genre, publish_date, users.username from user_stories 
                                    left join users on user_stories.user_id = users.user_id where story_id=" . $story_to_view);

        $story_details->execute();

        $story_details->bind_result($title, $body, $link, $genre, $pub_date, $pub_user);

        echo "<div>\n";
        echo "<ul class='story-body'>\n";
        while($story_details->fetch()) {
            echo "<li class='story-entry'>";
                echo "<div class='story-entry-container'>\n";
                    echo "<div class='entry-header-container'>";
                        echo "<div class='story-entry-title'>";
                            printf("<h1>%s</h1>\n", $title);
                        echo "</div>";
                        echo "<div>";
                            printf("<h5 class='entry-pub-date'>Published on %s by %s</h5>\n", $pub_date, $pub_user);
                        echo "</div>";
                    echo "</div>";
                    echo "<div class='entry-body'>";
                        printf("<p class='story-entry-body'>%s</p>\n", $body);
                        printf("<h3 class='story-entry-body'><strong>Genre:</strong> %s</h3>\n", $genre);
                        printf("<h3 class='story-entry-body'><strong>Link:</strong> %s</h3>\n", $link);
                    echo "</div>\n";
                    echo "\t\t\t<form method='post'>\n";
                    echo "\t\t\t\t<input class='logout-btn' type='submit' name='add-comment' value='Comment'>\n";
                    echo "\t\t\t</form>\n";
                echo "</div>\n";
            echo "</li>";
        }
        echo "</ul>\n";
        echo "</div>\n";

        // Add comment functionality
        if (isset($_POST['add-comment'])) {
            if ($_SESSION['guest']) {
                echo "Uh oh! Looks like you are visiting as a guest\n";
                echo "Please login or create an account to be able to comment on stories.";
                echo "<form action='registration-page.php'>\n";
                echo "\t<input class='logout-btn' type='submit' name='go-register' value='Register'>\n";
                echo "</form>\n";
                echo "<form action='login-page.php'>\n";
                echo "\t<input class='logout-btn' type='submit' name='go-login' value='Login'>\n";
                echo "</form>\n";
            } else {
                echo "<form method='post'>";
                echo "<input type='hidden' name='token' value='" . $_SESSION['token'] . "'>";
                echo "<input class='comment-field' type='text' name='comment-text' placeholder='Your comment...'>";
                printf("<input type='hidden' name='story-for-comment' value='%u'>", $story_to_view);
                echo "<input class='logout-btn' type='submit' name='submit-comment' value='Submit Comment'>";
                echo "<input class='logout-btn' type='submit' name='cancel-comment' value='Cancel'>";
                echo "</form>";
            }
        }
        
        $story_details->close();

        $story_comments = $mysqli->prepare("select comment_id, comments.user_id, comment_text, upvote_value, DATE(timestamp), users.username 
                                            from comments left join users on comments.user_id = users.user_id where story_id=" . $story_to_view . " order by upvote_value desc, timestamp desc");

        $story_comments->execute();

        $story_comments->bind_result($comment_id, $commenter_id, $comment, $upvote_value, $timestamp, $commenter_user);

        // Display of comments made on selected story along with
        // options to edit/delete based on current user access
        echo "<div>";
        echo "<ul class='comment-list'>";
        while($story_comments->fetch()) {
            echo "<li class='comment-entry'>\n";
            echo "<div>";
                echo "<div class='comment-header-container'>";
                    echo "<div class='comment-header'>";
                        printf("<h2>%s</h2>", $commenter_user);
                    echo "</div>";
                    echo "<div class='comment-header'>";
                        printf("<h2>%s</h2>", $timestamp);
                    echo "</div>";
                echo "</div>";
                echo "<div class='comment-body-container'>";
                    echo "<div class='vote-field'>";
                        if (!$_SESSION['guest']) {
                        printf("<form method='post'>
                                <input type='hidden' name='token' value='" . $_SESSION['token'] . "'>
                                <input type='hidden' name='comment-of-vote' value='%u'>
                                <input class='vote-btn' type='submit' name='upvote-comment' value='+'>
                                <h1>%u</h1>
                                <input class='vote-btn' type='submit' name='downvote-comment' value='-'>
                                </form>", $comment_id, $upvote_value);
                        } else {
                            printf("<h1>%u</h1>", $upvote_value);
                        }
                    echo "</div>";
                    echo "<div class='comment-field'>";
                        printf("<h2>%s</h2>\n", $comment);
                    echo "</div>";
                    echo "<div class='comment-options-field'>";
                        if (!($_SESSION['guest']) && $current_user_id === $commenter_id) {
                        printf("<form method='post'><input type='hidden' name='modify-comment-id' value='%u'>
                            <input type='hidden' name='token' value='" . $_SESSION['token'] . "'>
                            <input class='logout-btn' type='submit' name='edit-own-comment' value='Edit'>
                            <input class='logout-btn' type='submit' name='delete-own-comment' value='Delete'>
                            </form>", $comment_id);
                        }
                        elseif (!($_SESSION['guest']) && $_SESSION['username'] === $pub_user) {
                            printf("<td><form method='post'>
                                    <input type='hidden' name='token' value='" . $_SESSION['token'] . "'>
                                    <input type='hidden' name='modify-comment-id' value='%u'>
                                    <input class='logout-btn' type='submit' name='delete-own-comment' value='Delete'>
                                    </form></td>", $comment_id);
                        }
                        if (isset($_POST['edit-own-comment'])) {
                            $edit_comment_id = $_POST['modify-comment-id'];

                            if ($comment_id == $edit_comment_id) {
                                printf("<form method='post'>
                                    <input type='hidden' name='token' value='" . $_SESSION['token'] . "'>
                                    <input type='hidden' name='modify-comment-id' value='%u'<label for='comment-edit'></label>
                                    <input class='edit-comment-field' type='text' name='comment-edit' value='%s'>
                                    <input class='logout-btn' type='submit' name='submit-comment-changes' value='Submit Changes'>
                                    </form>", $edit_comment_id, $comment);
                            }

                        }
                    echo "</div>";
                echo "</div>";
            echo "</div>";   
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";

        $story_comments->close();
        ?>
    </div>
</body>
</html>
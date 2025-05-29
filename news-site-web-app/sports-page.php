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
if (isset($_POST['hist-filter'])) {
    header("Location: history-page.php");
}   
if (isset($_POST['user-stories'])) {
    header("Location: user-stories.php");
}   
if (isset($_POST['post-story'])) {
    header("Location: post-story.php");
}
if (isset($_POST['view-story'])) {
    $_SESSION['story-to-view'] = $_POST['story-to-view'];
    header("Location: view-story.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports News</title>
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
    <!-- A list of stories relating to sports -->
    <div>
        <h2 class='body-title'>Sports News</h2><br>
        <?php
        require 'database.php';

        $stmt = $mysqli->prepare("select story_id, title, body, link, genre, DATE_FORMAT(publish_date, '%m-%d-%Y'), users.username from user_stories 
                                    left join users on user_stories.user_id = users.user_id where genre='Sports' order by publish_date desc limit 15");

        $stmt->execute();

        $stmt->bind_result($story_id, $title, $body, $link, $genre, $pub_date, $user);

        echo "<div>\n";
        echo "<ul class='story-body'>\n";
        while($stmt->fetch()) {
            echo "<li class='story-entry'>";
                echo "<div class='story-entry-container'>\n";
                    echo "<div class='entry-header-container'>";
                        echo "<div class='story-entry-title'>";
                            printf("<form method='post'><input type='hidden' name='story-to-view' value='%u'><input class='story-entry-title' type='submit' name='view-story' value='%s'></form>\n", $story_id, $title);
                        echo "</div>";
                        echo "<div>";
                            printf("<h5 class='entry-pub-date'>Published on %s by %s</h5>\n", $pub_date, $user);
                        echo "</div>";
                    echo "</div>";
                    echo "<div class='entry-body'>";
                        printf("<p class='story-entry-body'>%s</p>\n", $body);
                        printf("<h3 class='story-entry-body'><strong>Genre:</strong> %s</h3>\n", $genre);
                        printf("<h3 class='story-entry-body'><strong>Link:</strong> %s</h3>\n", $link);
                    echo "</div>\n";
                echo "</div>\n";
            echo "</li>";
        }
        echo "</ul>\n";
        echo "</div>\n";

        $stmt->close();
        ?>
    </div>
    </body>
</html>
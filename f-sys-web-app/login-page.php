<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" type="text/css" href="login-page.css">
</head>
<body class="body-container">
    <div class="file-header-container">
        <!-- Website logo and brand -->
        <h1 class="file-header-text">F-SYS</h1>
        <h2 class="file-sub-text">The Most Secure File System Ever</h2>
    </div>
    <div class="login-container">
        <!-- Login Box -->
        <h3 class="login-text">Please Login<br>With Your Username</h3>
        <div>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
                <input class="login-input" type="text" name="username" id="username">
                <input class="login-button" type="submit" id="login" value="LOGIN">
            </form>
        </div>
    </div>
    
    <?php
        session_start();
        // If username is set, navigate to file-system
        if (isset($_POST['username'])) {
            $_SESSION['username'] = htmlentities($_POST['username']);
            $_SESSION['logged-in'] = true;
            header("Location: file-system.php");
        }

        // If invalid username is entered, display error indicating so
        if (isset($_SESSION['invalid-user-attempt']) and $_SESSION['invalid-user-attempt']) {
            echo "<h2 class='login-error-text'>Invalid username. Please try again</h2>";
        } 
    ?>
</body>
</html>
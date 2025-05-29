<?php
        require 'database.php';
        session_start();
        // If username is set, navigate to news-site
        if (isset($_POST['username'], $_POST['password'])) {
            $username = htmlentities($_POST['username']);
            $pwd_guess = htmlentities($_POST['password']);

            $user_exists = $mysqli->prepare("select COUNT(*), user_id, hashed_password from users where username=?");
            $user_exists->bind_param('s', $username);

            $user_exists->execute();
            $user_exists->bind_result($cnt, $user_id, $pwd_hash);
            $user_exists->fetch();

            // Compare the submitted password to the actual password hash
            if($cnt == 1 && password_verify($pwd_guess, $pwd_hash)){
                // Login succeeded!
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['logged-in'] = true;
                $_SESSION['guest'] = false;
                $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));

                // Redirect to your target page
                header("Location: news-site.php");
            } else {
                // Login failed; redirect back to the login screen
                echo "<h2>The username or password you entered is incorrect</h2>";
            }
            $user_exists->close();
        }

        if (isset($_POST['register-page-redirect'])) {
            header("Location: registration-page.php");
        }

        // If invalid username is entered, display error indicating so
        if (isset($_SESSION['invalid-user-attempt']) and $_SESSION['invalid-user-attempt']) {
            echo "<h2 class='login-error-text'>Invalid username. Please try again</h2>";
        } 

        if (isset($_POST['guest'])) {
            $_SESSION['logged-in'] = false;
            $_SESSION['guest'] = true;
            header("Location: news-site.php");
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" type="text/css" href="news-site.css">
</head>
<body class="login-container">
    <div class="header-container">
        <!-- Website Title -->
        <h1 class="header-text">The Daily Instance</h1>
    </div>
    <div class="login-container">
        <!-- Login Box -->
        <div class="login-form">
            <h3 class="login-text">Please Login<br>With Your Username</h3>
            <div>
                <!-- Account Login Form -->
                <form class="login-form" method="post">
                    <label for="username">Username</label>
                    <input class="login-input" type="text" name="username" id="username">
                    <label for="new-password">Password</label>
                    <input class="login-input" type="password" name="password" id="new-password">
                    <input class="logout-btn login-input" type="submit" id="login" value="Login">
                </form>
                <br>
                <!-- Account Registration Form -->
                <form method="post">
                    <label for="register-new-user">New User?</label>
                    <input class="logout-btn" type="submit" name="register-page-redirect" id="register-new-user" value="Register Here">
                </form>
                <!-- Guest Visit Form -->
                <form method="post">
                    <br><input class="logout-btn" type="submit" name="guest" id="guest" value="Continue as a guest">
                </form>
            </div>
        </div>
    </div>
</body>
</html>
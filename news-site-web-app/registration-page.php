<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" type="text/css" href="news-site.css">
</head>
<body class="body-container">
    <!-- Website Title -->
    <div class="header-container">
        <h1 class="header-text">The Daily Instance</h1>
    </div>
    <div class="login-container">
        <h3 class="body-title">Create an Account</h3>
        <div class='registration-form'>
            <!-- Registration Form -->
            <form class='edit-story-form' method="post">
                <label for="username">Username</label>
                <input class="register-input" type="text" name="username" id="new-username" minlength="6">
                <label for="email">Email Address</label>
                <input class="register-input" type="email" name="email" id="new-email" minlength="6">
                <label for="password">Password</label>
                <input class="register-input" type="password" name="password" id="new-password" minlength="10" pattern="[A-Za-z0-9]{8,}">
                <label for="confirm-password">Confirm Password</label>
                <input class="register-input" type="password" name="confirm-password" id="new-confirm-password" minlength="10" pattern="[A-Za-z0-9]{8,}"> 
                <br><h5>Passwords should contain an uppercase letter, a lowercase letter, and a number. </h5>
                <input class="logout-btn" type="submit" name="register" id="register" value="Register"><br>
            </form>
            <!-- Login redirect -->
            <form method="post">
                <label for="register-new-user">Already a User?</label>
                <input class='logout-btn' type="submit" name="login-page-redirect" id="register-new-user" value="Login Here">
            </form>
        </div>
    </div>
    
    <!-- Inserting user into user database -->
    <?php
        require 'database.php';
        if (isset($_POST['login-page-redirect'])) {
            header("Location: login-page.php");
        }
        if (isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['confirm-password'])) {
            $username = htmlentities($_POST['username']);
            $email = htmlentities($_POST['email']);
            $pass = htmlentities($_POST['password']);
            $c_pass = htmlentities($_POST['confirm-password']);

            // Confirms passwords match
            if ($pass !== $c_pass) {
                echo "<h2> Your passwords do not match </h2>";
                exit;
            }

            $existing_user_query = $mysqli->prepare("select username from users where username=?");

            if (!$existing_user_query) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
    
            $existing_user_query->bind_param('s', $username);

            $existing_user_query->execute();

            $existing_user_query->bind_result($existing_user);

            // Ensures username is unique
            if (!$existing_user_query->fetch()) {
                $add_user_query = $mysqli->prepare("insert into users (username, email_address, hashed_password) values (?, ?, ?)");

                if (!$add_user_query) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                } else {
                    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                    $add_user_query->bind_param('sss', $username, $email, $hashed_pass);

                    $add_user_query->execute();
                    $add_user_query->close();
                    header("Location: login-page.php");
                }
            } else {
                echo "<h2>An account with this username already exists</h2>";
            }
            $existing_user_query->close();
        }
    ?>
</body>
</html>
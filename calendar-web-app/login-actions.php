<?php
    require 'database.php';
    ini_set("session.cookie_httponly", 1);
    session_start();

    $previous_ua = @$_SESSION['useragent'];
    $current_ua = $_SERVER['HTTP_USER_AGENT'];

    if (isset($_SESSION['useragent']) && $previous_ua !== $current_ua) {
        die("Session hijack detected");
    } else {
        $_SESSION['useragent'] = $current_ua;
    }

    header("Content-Type: application/json");

    $json_str = file_get_contents('php://input');
    //This will store the data into an associative array
    $json_obj = json_decode($json_str, true);

    //Variables can be accessed as such:
    $action = $json_obj['action'];

        // If username is set, navigate to news-site
    if ($action == 'login') {
        $username = $json_obj['username'];
        $password = $json_obj['password'];

        if (!($username || $password)) {
            echo json_encode([
                "success" => false,
                "message" => "No username or password entered"
            ]);
            exit;
        }
        
        $username = htmlentities($username);
        $password = htmlentities($password);

        $user_exists = $mysqli->prepare("select COUNT(*), id, hashed_password from users where username=?");
        $user_exists->bind_param('s', $username);

        $user_exists->execute();
        $user_exists->bind_result($cnt, $user_id, $pwd_hash);
        $user_exists->fetch();

        // Compare the submitted password to the actual password hash
        if($cnt == 1 && password_verify($password, $pwd_hash)){
            // Login succeeded!
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['logged-in'] = true;
            $_SESSION['guest'] = false;
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));

            echo json_encode([
                "success" => true,
                "message" => "Successfully logged in",
                "current_user" => $user_id
            ]);
        } else {
            // Login failed; redirect back to the login screen
            echo json_encode([
                "success" => false,
                "message" => "The username or password you entered is incorrect"
            ]);
        }
        $user_exists->close();
        exit;
    }

    if ($action == 'login-redirect') {
        echo json_encode([
            "success" => true,
            "message" => "Successfully redirected to login page"
        ]);
        exit;
    }
    if ($action =='register-redirect') {
        echo json_encode([
            "success" => true,
            "message" => "Successfully redirected to registration page"
        ]);
        exit;
    }

    if ($action == 'register') {
        $username = $json_obj['username'];
        $email = $json_obj['email'];
        $password = $json_obj['password'];
        $confirm_password = $json_obj['confirm-password'];

        if (!($username || $email || $password || $confirm_password)) {
            echo json_encode([
                "success" => false,
                "message" => "All fields must be filled in"
            ]);
            exit;
        }

        $username = htmlentities($username);
        $email = htmlentities($email);
        $password = htmlentities($password);
        $confirm_password = htmlentities($confirm_password);

        // Confirms passwords match
        if ($password !== $confirm_password) {
            echo json_encode([
                "success" => false,
                "message" => "Passwords do not match"
            ]);
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
            $add_user_query = $mysqli->prepare("insert into users (username, email, hashed_password) values (?, ?, ?)");

            if (!$add_user_query) {
                echo json_encode([
                    "success" => false,
                    "message" => "Error occurred during add_user_query"
                ]);
                exit;
            } else {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $add_user_query->bind_param('sss', $username, $email, $hashed_pass);

                $add_user_query->execute();
                $add_user_query->close();
               
                echo json_encode([
                    "success" => true,
                    "message" => "Successfully registered new user"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Username already in use"
            ]);
        }

        $existing_user_query->close();
        exit;
    }

    if ($action == 'guest-view') {
        $_SESSION['logged-in'] = false;
        $_SESSION['guest'] = true;
        echo json_encode([
            "success" => true,
            "message" => "You have successfully entered the app as a guest"
        ]);
        exit;
    }

    if ($action = 'get-session') {
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                "success" => true,
                "message" => "User still logged in"
            ]);
            exit;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "User not logged in"
            ]);
            exit;
        }
    }
?>
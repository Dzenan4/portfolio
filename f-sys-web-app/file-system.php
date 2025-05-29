<?php
    session_start();

    if (!$_SESSION['logged-in']) {
        header("Location: login-page.php");
    }
    function verify_user($username) {

        // Verify that username field is not left blank upon clicking login
        if ($username === "") {
            return false;
        }

        // Check if the provided username is in users.txt
        $h = fopen("/srv/module2group/users.txt", "r");
        $linenum = 1;

        while( !feof($h) ) {
            $user = fgets($h);
            $user = str_replace("\n", "", $user);
            if (strcmp($username, $user) === 0) {
                fclose($h);
                return true;
            }
        }

        fclose($h);
        return false;
    }

    // If username is in users.txt, show file contents
    // If not, redirect to login page and set invalid user flag
    $username = $_SESSION['username'];
    if (!verify_user($username)) {
        header("Location: login-page.php");
        $_SESSION['invalid-user-attempt'] = true;
        $_SESSION['logged-in'] = false;
    } else {
        $_SESSION['invalid-user-attempt'] = false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User File System Page</title>
    <link rel="stylesheet" type="text/css" href="file-system.css">
</head>
<body>
    <div class="file-header-container">
        <!-- Website logo and brand -->
        <h1 class="file-header-text">F-SYS</h1>
        <h2 class="file-sub-text">The Most Secure File System Ever</h2>
    </div>
    <div class="file-container">
        <!-- Welcome message -->
        <h1> Hello, <?php echo $_SESSION['username']?>. Here are your files: </h1>
        <form action="process-filter.php" method="post">
            <!-- Dropdown for filter options -->
            <label for="filter">Filter: </label>
            <select class="filter-select" name="filter" id="filter">
                <option value="ascending" <?php if ($_SESSION['filter'] === "ascending") { echo "selected='selected'"; } ?> >Ascending</option>
                <option value="descending" <?php if ($_SESSION['filter'] === "descending") { echo "selected='selected'"; } ?> >Descending</option>
            </select>
            <input class="file-action" type="submit" value="Filter">
        </form>
    </div>
    <div class="file-container">
        <!-- List of files belonging to user -->
        <ul class="file-list">
            <?php
                $filter = strcmp(($_SESSION['filter']), "ascending") === 0 ? "ASC" : "DESC";
                $username = $_SESSION['username'];
                $user_files = scandir("/srv/module2group/".$username);

                // Remove . and .. entries from user files
                $user_files = array_diff($user_files, array('.', '..'));

                // Sort files in alphabetical order
                sort($user_files, SORT_STRING | SORT_FLAG_CASE);

                // Reverse order of files if filter is set to desc
                if ($filter === "DESC") {
                    $user_files = array_reverse($user_files);
                }

                // Display files and file actions
                foreach($user_files as $count => $name) {
                    $file_name = explode('.', $name)[0];
                    $file_type = explode('.', $name)[1];
                    $delete_input_name = "delete-".$count;
                    $rename_input_name = "rename-".$count;
                    $copy_input_name = "copy-".$count;

                    echo "<li class='list-entry'>\n";
                    echo "\t\t\t\t<div class='list-entry-div'>\n";

                    echo "\t\t\t\t\t<div class='file-actions-container'>\n";

                    // Display file name and allow it to be clicked to view contents
                    // printf("\t\t<li class='list-entry'>\n\t\t\t<form action='redirect.php' method='get'>\n\t\t\t\t<input class='list-entry-file' type='submit' name='file' value=%s>\n\t\t\t</form>\n\t\t</li>\n", $name);

                    printf("\t\t\t\t\t\t<form action='redirect.php' method='get'>\n\t\t\t\t\t\t\t<input class='list-entry-file' type='submit' name='file' value=%s>\n\t\t\t\t\t\t</form>", $name);

                    echo "\t\t\t\t\t</div>\n";

                    echo "\t\t\t\t\t<div class='file-actions-container'>\n";

                    // Display rename option
                    printf("\t\t\t\t\t\t<form method='post'>\n\t\t\t\t\t\t\t<input class='list-entry-action' type='submit' name='".$rename_input_name."' value='Rename'>\n\t\t\t\t\t\t</form>\n");

                    // Display copy file option
                    printf("\t\t\t\t\t\t<form method='post'>\n\t\t\t\t\t\t\t<input class='list-entry-action' type='submit' name='".$copy_input_name."' value='Copy File'>\n\t\t\t\t\t\t</form>\n");

                    // Display delete fiile option
                    printf("\t\t\t\t\t\t<form method='post'>\n\t\t\t\t\t\t\t<input class='list-entry-action' type='submit' name=%s value='Delete'>\n\t\t\t\t\t\t</form>\n", 
                            $delete_input_name);

                    echo "\t\t\t\t\t</div>\n";

                    echo "\t\t\t\t</div>\n";

                    echo "\t\t\t</li>\n";

                    // If file deleted, redirect so page refreshes to reflect change
                    if (isset($_POST[$delete_input_name])) {
                        unlink("/srv/module2group/".$_SESSION['username']."/".$name);
                        header("Location: redirect.php");
                    }

                    // If rename is pressed, display text input and confirm/cancel options
                    if (isset($_POST[$rename_input_name])) {
                        echo "<form method='post'>";
                        echo "<label for='rename-file-name".$count."'>Enter a name for the file: </label>";
                        echo "<input class='rename-file-input' type='text' name='rename-file-name-".$count."'>";
                        echo "<input class='list-entry-action rename-file-button' type='submit' name='rename-submit-".$count."' value='Confirm'>";
                        echo "<input class='list-entry-action rename-file-button' type='submit' name='cancel-rename-'".$count."' value='Cancel'>";
                        echo "</form>";
                    } 

                    // If rename confirmed, rename file and redirect to reflect changes
                    if (isset($_POST['rename-submit-'.$count])) {
                        rename("/srv/module2group/".$username."/".$name, 
                                "/srv/module2group/".$username."/".htmlentities($_POST['rename-file-name-'.$count]).".".$file_type);
                        header("Location: redirect.php");
                    } 

                    // If rename cancelled, unset post variables and redirect to hide rename information
                    if (isset($_POST['cancel-rename-'.$count])) {
                        unset($_POST[$rename_input_name]);
                        unset($_POST['rename-file-name-'.$count]);
                        unset($_POST['rename-submit-'.$count]);
                        unset($_POST['cancel-rename-'.$count]);
                        header("Location: redirect.php");
                    }

                    // If copy file clicked, make copy and redirect to reflect changes
                    if (isset($_POST[$copy_input_name])) {
                        copy("/srv/module2group/".$username."/".$name, "/srv/module2group/".$username."/".$file_name."-1.".$file_type);
                        header("Location: redirect.php");
                    }
                }
            ?>
        </ul>
    </div>

    <div class="file-actions-container">
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
            <input class="file-action" type="submit" name="logout" value="Logout">
            <?php
            // If logout clicked, destroy session and redirect to login page
            if (isset($_POST['logout'])) {
                $_SESSION['logged-in'] = false;
                session_destroy();
                header("Location: login-page.php");
                exit;
            }
            ?>
        </form>

        <!-- Upload file input -->
        <form enctype="multipart/form-data" action="file-uploader.php" method="POST">
            <p>
                <input type="hidden" name="MAX_FILE_SIZE" value="20000000">
                <input class="file-upload-input" name="uploadedfile" type="file" id="uploadfile_input">
                <input class="file-action" type="submit" value="Upload">
            </p>
        </form>
    </div>
    <div class='invalid-upload-container'>
        <?php 
        // If upload button clicked with no file selected, display error message
            if ($_SESSION['invalid-file-upload'] === TRUE) {
		        echo "<h2 class='error-text'>Invalid file. Please upload a valid file.</h2>";
                $_SESSION['invalid-file-upload'] = false;
            }
        ?>
    </div>
</body>
</html>
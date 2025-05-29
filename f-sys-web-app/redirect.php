<?php
    session_start();

    // If file is to be viewed, redirect to viewing page
    if (isset($_GET['file'])) {
        // $file_number = $_GET['file-number'];
        // $file = $_GET['file-'.$file_number];
        $file = $_GET['file'];
        header("Location: file-viewer.php?file=".urlencode("$file"));
        exit;
    } else {
        // redirect back to file system to reflect any changes from copy, delete, or rename
        header("Location: file-system.php");
        exit;
    }
?>
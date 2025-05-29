<?php
    session_start();

    // Process filter selection 
    if (isset($_POST['filter'])) {
        $_SESSION['filter'] = $_POST['filter'];
    } else {
        $_SESSION['filter'] = "Ascending";
    }

    header("Location: file-system.php");
?>
<?php
	include "file-path-gen.php";
	session_start();

	// Get the filename and make sure it is valid
	$filename = basename($_FILES['uploadedfile']['name']);
	if (!preg_match('/^[\w_\.\-]+$/', $filename) ){
		$_SESSION['invalid-file-upload'] = TRUE;
		header("Location: file-system.php");
		exit;
	}
	$_SESSION['invalid-file-upload'] = FALSE;

	// Get the username and make sure it is valid
	$username = $_SESSION['username'];
	if (!preg_match('/^[\w_\-]+$/', $username) ){
		exit;
	}

	// Call method from 'file-path-gen' to generate file path
	$full_path = generate_file_path($username, $filename);

	// Return to file page
	if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $full_path) ){
		header("Location: file-system.php");
		exit;
	} else {
		header("Location: file-system.php");
		exit;
	}

?>
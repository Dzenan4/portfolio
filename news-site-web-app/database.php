<?php
// Content of database.php
$mysqli = new mysqli('localhost', 'php_user', 'php_pass', 'news_website');

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}
?>
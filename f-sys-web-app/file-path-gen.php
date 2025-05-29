<?php
    // Generate file path from username and filename
    function generate_file_path($username, $filename) {
        $full_path = sprintf("/srv/module2group/%s/%s", $username, $filename);
        return $full_path;
    }
?>
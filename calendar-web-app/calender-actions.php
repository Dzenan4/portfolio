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

    // Log user out of page
    if ($action == 'logout') {
        $_SESSION['logged-in'] = false;

        echo json_encode([
            "success" => true,
            "message" => "Successfully logged out of calender app"
        ]);
        session_destroy();
        exit;
    } else if ($action == 'add-event') {
        // prevents a guest from adding an event
        if ($_SESSION['guest']) {
            echo json_encode([
                "success" => false,
                "message" => "Create an account to add events!",
                "error" => "guest-account"
            ]);
            exit;
        }

        if (!hash_equals($_SESSION['token'], $json_obj['token'])){
            die("Request forgery detected");
        }

        $title = trim($json_obj['title']);
        $date = $json_obj['date'];
        $time = $json_obj['time'];
        $tag = $json_obj['tag'];
        $color = $json_obj['color'];
        $group_members = $json_obj['group-members'];


        if ($title ==  '' || $date == '' || $time == '') {
            echo json_encode([
                "success" => false,
                "message" => "An event must have a title, date, and time." . $title . ", " . $date . ", " . $time
            ]);
            exit;
        }

        $add_event = $mysqli->prepare("insert into events(title, date, time, user_id, tag, color) values (?, ?, ?, ?, ?, ?)");

        if(!$add_event){
            echo json_encode([
                "success" => false,
                "message" => "An error occurred with the insert event query"
            ]);
            exit;
        }


        $add_event->bind_param('sssiss', $title, $date, $time, $_SESSION['user_id'], $tag, $color);

        $add_event->execute();

        $event_id = $mysqli->insert_id;

        $add_event->close();


        foreach($group_members as $member) {
            if ($member == $_SESSION['username']) {
                continue;
            }

            $get_member_id = $mysqli->prepare("select COUNT(*), id from users where username=?");
            $get_member_id->bind_param('s', $member);
            $get_member_id->execute();
            $get_member_id->bind_result($cnt, $member_id); 
            $get_member_id->fetch();

            if ($cnt < 1) {
                $get_member_id->close();
                continue;
            }

            $mem_id = $member_id;
            $get_member_id->close();

            $add_to_groups = $mysqli->prepare("insert into group_events (event_id, user_id) values (?, ?)"); 
            $add_to_groups->bind_param('ii', $event_id, $mem_id);            
            $add_to_groups->execute();
            $add_to_groups->close();
        }

        echo json_encode([
            "success" => true,
            "message" => "Successfully saved new event: " . $title,
            "data" => $event_id
        ]);
        
        exit;

    } else if ($action == 'get-user-events') {
        // guests have no events
        if ($_SESSION['guest']) {
            echo json_encode([
                "success" => true,
                "message" => "Successfully queried for day events",
                "data" => array(),
            ]);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        $event_items = array();

        $events = $mysqli->prepare("select id, title, date, time, tag, color from events where user_id = " . $user_id);
        $events->execute();
        $events->bind_result($id, $title, $date, $time, $tag, $color);

        while ($events->fetch()) {
            array_push($event_items, array($id, htmlentities($title), $date, $time, $tag, $color, "User Event"));
        }

        $events->close();
        $get_shared_users = $mysqli->prepare("select sharer_id from shared_calendars where receiver_id = " . $user_id);
        $get_shared_users->execute();
        $get_shared_users->bind_result($sharer_id);

        $shared_users = array();
        while ($get_shared_users->fetch()) {
            array_push($shared_users, $sharer_id);
        }   
        $get_shared_users->close();

        foreach($shared_users as $sharer_id) {
            $get_shared_events = $mysqli->prepare("select id, title, date, time, tag, color from events where user_id = " . $sharer_id);
            $get_shared_events->execute();
            $get_shared_events->bind_result($id, $title, $date, $time, $tag, $color);
            while ($get_shared_events->fetch()) {
                array_push($event_items, array($id, htmlentities($title), $date, $time, $tag, $color, "Shared Event"));
            }
            $get_shared_events->close();
        }

        $get_group_events = $mysqli->prepare("select event_id from group_events where user_id = " . $user_id);
        $get_group_events->execute();
        $get_group_events->bind_result($group_event_id);

        $group_events = array();
        while ($get_group_events->fetch()) {
            array_push($group_events, $group_event_id);
        }   
        $get_group_events->close();

        foreach($group_events as $group_event_id) {
            $get_event = $mysqli->prepare("select id, title, date, time, tag, color from events where id = " . $group_event_id);
            $get_event->execute();
            $get_event->bind_result($id, $title, $date, $time, $tag, $color);
            while ($get_event->fetch()) {
                array_push($event_items, array($id, htmlentities($title), $date, $time, $tag, $color, "Group Event"));
            }
            $get_event->close();
        }

        echo json_encode([
            "success" => true,
            "message" => "Successfully queried for day events",
            "data" => $event_items,
            "token" => $_SESSION['token']
        ]);
        exit;

    } else if ($action == 'delete-event') {
        if (!hash_equals($_SESSION['token'], $json_obj['token'])){
            die("Request forgery detected");
        }

        $user_id = $_SESSION['user_id'];
        $event_id = $json_obj['id'];
        $title = trim($json_obj['title']);
        $date = ($json_obj['date']);
        $time = $json_obj['time'];
        $tag = $json_obj['tag'];
        $color = $json_obj['color'];

        $get_event_creator = $mysqli->prepare("select user_id from events where id=?");
        $get_event_creator->bind_param('i', $event_id);
        $get_event_creator->execute();
        $get_event_creator->bind_result($creator_id);
        $get_event_creator->fetch();
        $get_event_creator->close();

        $shared_event_ids = array();
        if ($creator_id == $user_id) {
            $delete_event = $mysqli->prepare("delete from events where id=?");
            $delete_event->bind_param('i', $event_id);
            $delete_event->execute();
            array_push($shared_event_ids, array($event_id, $date));
            $delete_event->close();
        } else {
            $check_group_event = $mysqli->prepare("select COUNT(*) from group_events where event_id = ? and user_id = ?");
            $check_group_event->bind_param('ii', $event_id, $user_id);
            $check_group_event->execute();
            $check_group_event->bind_result($cnt_group);
            $check_group_event->fetch();
            $check_group_event->close();

            if ($cnt_group > 0) {
                $get_shared_events = $mysqli->prepare("select id, date from events where id = ?");
                $get_shared_events->bind_param('i', $event_id);
                $get_shared_events->execute();
                $get_shared_events->bind_result($event_id, $event_date);
                while ($get_shared_events->fetch()) {
                    array_push($shared_event_ids, array($event_id, $event_date));
                }
                $get_shared_events->close();

                $delete_group_event = $mysqli->prepare("delete from group_events where event_id = ? and user_id = ?");
                $delete_group_event->bind_param('ii', $event_id, $user_id);
                $delete_group_event->execute();
                $delete_group_event->close();
            } else {
                $get_shared_events = $mysqli->prepare("select id, date from events where user_id = ?");
                $get_shared_events->bind_param('i', $creator_id);
                $get_shared_events->execute();
                $get_shared_events->bind_result($event_id, $event_date);
                while ($get_shared_events->fetch()) {
                    array_push($shared_event_ids, array($event_id, $event_date));
                }
                $get_shared_events->close();

                $check_shared_event = $mysqli->prepare("delete from shared_calendars where sharer_id = ? and receiver_id = ?");
                $check_shared_event->bind_param('ii', $creator_id, $user_id);
                $check_shared_event->execute();
                $check_shared_event->close();
            }
        }

        echo json_encode([
            "success" => true,
            "message" => "Successfully deleted event",
            "deleted_events" => $shared_event_ids
        ]);

    } else if ($action == 'update-event') {
        if (!hash_equals($_SESSION['token'], $json_obj['token'])){
            die("Request forgery detected");
        }

        $user_id = $_SESSION['user_id'];
        $event_id = $json_obj['id'];
        $title = trim($json_obj['title']);
        $date = $json_obj['date'];
        $time = $json_obj['time'];
        $tag = $json_obj['tag'];
        $color = $json_obj['color'];

        $update_event = $mysqli->prepare("update events set title = ?, date = ?, time = ?, tag = ?, color = ? where id=?");
        $update_event->bind_param('sssssi', $title, $date, $time, $tag, $color, $event_id);
        $update_event->execute();

        echo json_encode([
            "success" => true,
            "message" => "Successfully updated event"
        ]);
    } else if ($action == 'share-calendar') {
        // prevents guests from sharing calendar
        if ($_SESSION['guest']) {
            echo json_encode([
                "success" => false,
                "message" => "Create an account to add events and share your calendar",
                "error" => "guest-account"
            ]);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $receiver_username = $json_obj['receiver'];

        $get_receiver_id = $mysqli->prepare("select COUNT(*), id from users where username=?");
        $get_receiver_id->bind_param('s', $receiver_username);
        $get_receiver_id->execute();
        $get_receiver_id->bind_result($cnt, $receiver_id);
        $get_receiver_id->fetch();

        if ($cnt == 0) {
            echo json_encode([
                "success" => false,
                "message" => "No such user exists",
            ]);
            exit;
        } 

        $get_receiver_id->close();

        $share_calendar = $mysqli->prepare("insert into shared_calendars (sharer_id, receiver_id) values (?, ?)");
        $share_calendar->bind_param('ii', $user_id, $receiver_id);
        $share_calendar->execute();

        echo json_encode([
            "success" => true,
            "message" => "Successfully shared calendar with " . $receiver_username,
        ]);
        exit;
    }
?>
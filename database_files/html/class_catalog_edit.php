<?php

    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  

    // CONNECTION
    $config = parse_ini_file('../../../mysql.ini');
    $queries_dir = "../queries/";
    $dbname = 'ku_registrar';
    $conn = new mysqli(
                $config['mysqli.default_host'],
                $config['mysqli.default_user'],
                $config['mysqli.default_pw'],
                $dbname);

    if ($conn->connect_errno) {
        echo "Error: Failed to make a MySQL connection, here is why: ". "<br>";
        echo "Errno: " . $conn->connect_errno . "\n";
        echo "Error: " . $conn->connect_error . "\n";
        exit; // Quit this PHP script if the connection fails.
    }

    // import our custom php functions
    require "library.php";

    // TOGGLE LIGHT/DARK MODE
    $mode = 'mode';
    $light = "light";
    $dark = "dark";

    if(!array_key_exists($mode, $_COOKIE)){
        setcookie($mode, $light, 0, "/", "", false, true); //default
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(array_key_exists("toggle_mode", $_POST)){
        $new_mode = $light;
        if($_COOKIE[$mode] == $light){ $new_mode = $dark;}
        if($_COOKIE[$mode] == $dark){ $new_mode = $light;}
        setcookie($mode, $new_mode, 0, "/", "", false, true);
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    // START SESSION
    session_start();

    if(array_key_exists('logout', $_POST)){
        session_unset();
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(isset($_POST['username'])){
        $_SESSION['username'] = $_POST['username'];
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    //add recs
    if(array_key_exists('add_records', $_POST)){

        $course_id = (int) $_POST["courses"];
        $section = $_POST["section"];
        $term_id = (int) $_POST["terms"];
        $professor_id = (int) $_POST["professors"];
        list($building_name, $room_number) = explode(',', $_POST["locations"]);
        $room_number = (int) $room_number;
        $meeting_day_id = (int) $_POST["meeting_days"];
        list($time_start, $time_end) = explode(',', $_POST["meeting_times"]);

        //query
        $add_query = file_get_contents($queries_dir . 'classes_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('isiisiiss', $course_id, $section, $term_id, $professor_id, $building_name, $room_number, $meeting_day_id, $time_start, $time_end);
        $add_stmt->execute();
        
        //refresh
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    


    //more sql setups
    $query = "SELECT * FROM classes_view";
    $select_stmt = $conn->prepare($query);
    if (!$select_stmt) {
        echo "Couldn't prepare statement!";
        echo exit;
    }
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $classes_list = $result->fetch_all(MYSQLI_BOTH);

    $need_reload = FALSE;
    //del rec
    if(array_key_exists('delbtn', $_POST)){

        $del_query = file_get_contents($queries_dir . "classes_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('i', $id);

        // $get_all_instrument_ids = "SELECT instrument_id FROM instruments";
        // $idlist = $conn->query($get_all_instrument_ids);

        for($i = 0; $i < $result->num_rows; $i++){
            $id = $classes_list[$i][0];
            if(array_key_exists('checkbox' . $id, $_POST)){
                $need_reload = TRUE;
                $del_stmt->execute();
                if(session_status() == PHP_SESSION_ACTIVE){
                    $_SESSION['num_deleted'] = $_SESSION['num_deleted'] + 1;
                }
            }
        }
    }

    // ----- Reload this page if the database was changed.
    if($need_reload){ // This needs to be done before any output, to guarantee that it works.
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(array_key_exists('edit_records', $_POST)){

        $course_id = (int) $_POST["courses"];
        $section = $_POST["section"];
        $term_id = (int) $_POST["terms"];
        $professor_id = (int) $_POST["professors"];
        list($building_name, $room_number) = explode(',', $_POST["locations"]);
        $room_number = (int) $room_number;
        $meeting_day_id = (int) $_POST["meeting_days"];
        list($time_start, $time_end) = explode(',', $_POST["meeting_times"]);
        $class_max_capacity = (int) $_POST["class_max_capacity"];
        $class_id = (int) $_POST["original_class_id"];

        //query
        $edit_query = file_get_contents($queries_dir . 'classes_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
        $edit_stmt->bind_param('isiisiissii', $course_id, $section, $term_id, $professor_id, $building_name, $room_number, $meeting_day_id, $time_start, $time_end, $class_max_capacity, $class_id);
        $edit_stmt->execute();
        
        //refresh
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Catalog</title>
    <?php
        if($_COOKIE[$mode] == $light){
            ?><link rel="stylesheet" href="css/basic.css"><?php
        }elseif($_COOKIE[$mode] == $dark){
            ?><link rel="stylesheet" href="css/darkmode.css"><?php
        }
    ?>
</head>
<body>
    <h1>Class Catalog</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form> 
    <?php
        if(isset($_SESSION['username'])){
            ?><p>Welocome <?php echo $_SESSION['username']; ?></p>
            <form method="POST">
                <input type="submit" name="logout" value="Logout">
            </form><?php
        }else{
            ?><p>Enter name to start/resume session: </p>
            <form method="POST">
                <input type="text" name="username" placeholder="Enter name...">
                <input type="submit" value="Remember Me">
            </form><?php 
        }
    ?>

    <h2>Add Classes</h2>
    <?php
        generate_select_fields($conn, ["courses", "section", "terms", "professors", "locations", "meeting_days", "meeting_times"]);
    ?>

    
    <!-- more html -->  
    <h2>Delete Classes</h2>
    <?php 
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        result_to_html_table_with_del_checkbox($result); 
    ?>

    <h2>Edit Classes</h2>
    <?php
        //edit recs
        $select_stmt->execute();
        $result = $select_stmt->get_result();

        if(array_key_exists('edit_records', $_GET)){
            $row_index = $_GET['selected_record'];
            $result_dict = $result->fetch_all(MYSQLI_ASSOC);
            $_POST['original_class_id'] = $result_dict[$row_index]['class_id'];
            generate_select_fields($conn, ["courses", "section", "terms", "professors", "locations", "meeting_days", "meeting_times", "class_max_capacity"], $result_dict[$row_index]);
        }else{
            generate_edit_selections($result);
        }
    ?>
    
    <?php $conn->close(); ?>

</body>
</html>
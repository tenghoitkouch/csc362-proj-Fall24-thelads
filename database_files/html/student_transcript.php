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

    //more sql setups

    $query = "SELECT * FROM student_class_history_view";
    $select_stmt = $conn->prepare($query);
    if (!$select_stmt) {
        echo "Couldn't prepare statement!";
        echo exit;
    }
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $classes_list = $result->fetch_all();

    $need_reload = FALSE;

    if(array_key_exists('delbtn', $_POST)){

        $del_query = file_get_contents($queries_dir . "student_class_history_delete.sql");
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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php
        if($_COOKIE[$mode] == $light){
            ?><link rel="stylesheet" href="../css/basic.css"><?php
        }elseif($_COOKIE[$mode] == $dark){
            ?><link rel="stylesheet" href="../css/darkmode.css"><?php
        }
    ?>
</head>
<body>
    <h1>Title</h1>
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
    
    <!-- more html -->  
    <?php 
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        result_to_html_table_with_del_checkbox($result); 
    ?>

    

    <?php $conn->close(); ?>
</body>
</html>

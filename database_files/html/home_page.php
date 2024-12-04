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

    if(array_key_exists("login", $_POST)){
        $query = file_get_contents($queries_dir . 'users_select.sql');
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $_POST['user_name']);
        $stmt->execute();
        $result = $stmt->get_result();
        $result_dict = $result->fetch_assoc();
        $stored_hash_password = $result_dict['user_password'];
        $input_password = $_POST['user_password'];


        if(password_verify($input_password, $stored_hash_password)){
            session_start();

            foreach($result_dict as $key => $value){
                $_SESSION[$key] = $value;
            }
        }
    }

    if(array_key_exists('logout', $_POST)){
        session_unset();
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KU Registrar</title>
</head>
<body>
    <h1>Home</h1>
    <?php
        if(session_status() !== PHP_SESSION_ACTIVE){
            ?>
            <h2>Login</h2>
            <form method="POST">
                <label for="user_name">Username</label>
                <input type="text" name="user_name" id="user_name">
                <label for="user_password">Password</label>
                <input type="password" name="user_password" id="user_password">
                <input type="submit" name="login" value="login">
            </form>
            <?php
        }elseif (session_status() == PHP_SESSION_ACTIVE){
            echo '<h2>Hi ' .  $_SESSION['user_name'] . '</h2>';
            ?>
                <form method="post">
                    <input type="submit" name='logout' value="logout">
                </form>
            <?php

            if ($_SESSION['designation'] == 'student'){
                ?>
                    <h2>Student Links</h2>
                    <ul>
                        <li><a href="transcript.php">Transcript</a></li>
                        <li><a href="">Schedule</a></li>
                        <li><a href="degree_requirements.php">Degree Requirements</a></li>
                        <li><a href="class_catalog.php">Class Catalog</a></li>
                        <li><a href="">Class Registration</a></li>
                    </ul>
                <?php
            }elseif ($_SESSION['designation'] == 'admin'){
                ?>
                    <h2>Admin Links</h2>
                    <ul>
                        <li><a href="">Transcript</a></li>
                        <li><a href="">Degree Requirements</a></li>
                        <li><a href="courses_admin">Courses</a></li>
                        <li><a href="class_catalog_admin.php">Classes</a></li>
                        <li><a href="">Buildings</a></li>
                        <li><a href="">Locations</a></li>
                        <li><a href="">Professors</a></li>
                        <li><a href="">Students</a></li>
                        <li><a href="">Terms</a></li>
                        <li><a href="">Meeting Days</a></li>
                        <li><a href="">Meeting Times</a></li>
                    </ul>
                <?php
            }
        }

    ?>
</body>
</html>
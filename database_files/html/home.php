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
    session_start();

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
            $_SESSION['logged_in'] = TRUE;

            foreach($result_dict as $key => $value){
                $_SESSION[$key] = $value;
            }
        }
    }

    if(array_key_exists('logout', $_POST)){
        session_unset();
        $_SESSION['logged_in'] = FALSE;

        header("Location: home.php", true, 303);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KU Registrar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Kendianawa University Registrar</h1>
        <nav>
            <?php build_nav(); ?>
        </nav>
    </header>
    <main>
        <h2>Home</h2>
        
        <?php
        if (isset($_SESSION['logged_in']) == FALSE || $_SESSION['logged_in'] == FALSE){
            ?>
            <h3>Login</h3>
            <form method="POST">
                <label for="user_name">Username</label>
                <input type="text" name="user_name" id="user_name"><br>
                <label for="user_password">Password</label>
                <input type="password" name="user_password" id="user_password"><br>
                <button type="submit" name="login">Login</button>
            </form>
            <?php
        }
        ?>

        <?php
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == TRUE){
            echo '<h2>Hi ' .  $_SESSION['user_name'] . '</h2>';

            ?>
            <p>page content</p>
            <?php
        }
        ?>
        
    </main>

    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
</body>
</html>
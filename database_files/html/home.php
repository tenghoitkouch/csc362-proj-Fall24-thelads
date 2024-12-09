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
            //echo '<h2>Hi ' .  $_SESSION['user_name'] . '</h2>';

            ?>
            <h2>Welcome to Kendianawa University Registrar</h2>
            <p>
                At Kendianawa University, we are dedicated to providing top-tier academic services to our students, faculty, and staff. 
                Our registrar's office ensures seamless management of student records, course enrollments, and academic achievements.
            </p>

            <section>
                <h3>What We Offer</h3>
                <ul>
                    <li><strong>Student Records:</strong> Secure and accurate record-keeping for all students.</li>
                    <li><strong>Course Enrollment:</strong> Easily manage your semester schedule and course registrations.</li>
                    <li><strong>Graduation Services:</strong> Guidance and support for a smooth transition to graduation.</li>
                </ul>
            </section>

            <section>
                <h3>Upcoming Deadlines</h3>
                <ul>
                    <li><strong>Spring Semester Registration:</strong> November 13, 2024</li>
                    <li><strong>Last Day to Drop Courses:</strong> February 10, 2024</li>
                </ul>
            </section>

            <section>
                <h3>Resources</h3>
                <p>
                    Need help? Explore our comprehensive resources to guide you through every step of your academic journey.
                </p>
                <ul>
                    <li><a href="class_catalog.php">Class Catalog</a></li>
                    <li><a href="#">Contact the Registrar</a></li>
                </ul>
            </section>

            <?php
        }
        ?>
        
    </main>

    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
</body>
</html>
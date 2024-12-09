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

    if(array_key_exists('logout', $_POST)){
        session_unset();
        $_SESSION['logged_in'] = FALSE;

        header("Location: home.php", true, 303);
        exit;
    }
    
    //more sql setups
    $query = "SELECT * FROM classes_view";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_all(MYSQLI_BOTH);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Catalog</title>
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
        <h2>Class Catalog</h2>
        <?php //result_to_html_table($result); ?>
        <form action="" method="get">

        <table>
            <thead>
                <tr>
                    <?php
                        foreach(array_keys($result[0]) as $key){
                            if (!is_numeric($key)) {
                                echo '<td><b>' . $key . '</b></td>';
                            }
                        }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($result as $row => $row_data){
                        ?>
                            <tr>
                                <?php
                                    foreach($row_data as $key => $value){
                                        if (!is_numeric($key)) {
                                            echo '<td>' . $value . '</td>';
                                        }
                                    }
                                ?>
                            </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>
        </form>

    </main>
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
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

    $terms_query = 'SELECT * FROM terms_view';
    $terms_select_stmt = $conn->prepare($terms_query);
    $terms_select_stmt->execute();
    $terms_result = $terms_select_stmt->get_result();
    $terms_result_both = $terms_result->fetch_all(MYSQLI_BOTH);

    $student_id = (int) $_SESSION['designation_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
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
        <h2>Schedule</h2>
        <?php
        if(array_key_exists('terms_index', $_GET)){
            $term_start_date = $terms_result_both[$_GET['terms_index']]['term_start_date'];
            $term_end_date = $terms_result_both[$_GET['terms_index']]['term_end_date'];
            $term = $term_start_date . ' - ' . $term_end_date;
            
            //get all student's classes in term
            $student_class_history_query = file_get_contents($queries_dir . 'student_class_history_select_by_term.sql');
            $student_class_history_select_stmt = $conn->prepare($student_class_history_query);
            $student_class_history_select_stmt->bind_param('is', $student_id, $term);
            $student_class_history_select_stmt->execute();
            $student_class_history_result = $student_class_history_select_stmt->get_result();
            //$student_class_history_result_both = $student_class_history_result->fetch_all(MYSQLI_BOTH);

            result_to_html_table($student_class_history_result);

        }else{
            ?>
            <form method="get">
                <label for="terms_index">Select Term</label>
                <select name="terms_index" id="terms_index" required>
                    <?php foreach ($terms_result_both as $index => $term){ ?>
                        <option value="<?php echo $index ?>">
                            <?php echo $term['term_start_date'] . ' - ' . $term['term_end_date']; ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit">Submit</button>
            </form> 
        <?php } ?>
    </main>    
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
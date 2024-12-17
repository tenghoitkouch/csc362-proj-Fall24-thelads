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
    $terms_query = 'SELECT * FROM terms_view';
    $terms_select_stmt = $conn->prepare($terms_query);
    $terms_select_stmt->execute();
    $terms_result = $terms_select_stmt->get_result();
    $terms_result_both = $terms_result->fetch_all(MYSQLI_BOTH);

    $student_id = (int) $_SESSION['designation_id'];

    // Get all student's classes on the waitlist for the term
    $classes_waitlist_query = file_get_contents($queries_dir . 'classes_waitlist_select_by_term.sql');
    $classes_waitlist_select_stmt = $conn->prepare($classes_waitlist_query);
    $classes_waitlist_select_stmt->bind_param('is', $student_id, $term);
    $classes_waitlist_select_stmt->execute();
    $classes_waitlist_result = $classes_waitlist_select_stmt->get_result();
    $classes_waitlist_result_both = $classes_waitlist_result->fetch_all(MYSQLI_BOTH);

    //get all classes in term
    $classes_query = 'SELECT * FROM classes_view';
    $classes_select_stmt = $conn->prepare($classes_query);
    $classes_select_stmt->execute();
    $classes_result = $classes_select_stmt->get_result();
    $classes_result_both = $classes_result->fetch_all(MYSQLI_BOTH);

    if(isset($_GET['terms_index'])){
        $term_start_date = $terms_result_both[$_GET['terms_index']]['term_start_date'];
        $term_end_date = $terms_result_both[$_GET['terms_index']]['term_end_date'];
        $term = $term_start_date . ' - ' . $term_end_date;

        $temp = [];
        foreach($classes_result_both as $index => $class){
            if($class['term'] == $term){
                $temp[$index] = $class;
            }
        }
        $classes_result_both = $temp;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Waitlist</title>
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
        <h2>Class Waitlist</h2>
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

        <?php
            echo '<h3>Class Catalog</h3>';
            result_to_html_table_with_checkbox($classes_result_both, 'Add?', 'selected[]', 'class_id', 'Add Records', 'add_records');

        ?>
    </main>    
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
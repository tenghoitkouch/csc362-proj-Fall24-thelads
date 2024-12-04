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

    //more sql setups
    $terms_query = 'SELECT * FROM terms_view';
    $terms_select_stmt = $conn->prepare($terms_query);
    $terms_select_stmt->execute();
    $terms_result = $terms_select_stmt->get_result();
    $terms_result_both = $terms_result->fetch_all(MYSQLI_BOTH);

    $student_id = (int) $_SESSION['designation_id'];



    //add recs
    if(array_key_exists('add_records', $_POST)){

        $class_ids = $_POST['selected'];        

        //query
        $add_query = file_get_contents($queries_dir . 'student_class_history_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('ii', $student_id, $class_id);

        foreach($class_ids as $value){
            $class_id = (int) $value;            
            $add_stmt->execute();
        }

        //refresh
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    $need_reload = FALSE;
    //del rec
    if(array_key_exists('delete_records', $_POST)){

        $del_query = file_get_contents($queries_dir . "student_class_history_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('ii', $student_id, $class_id);

        $class_ids = $_POST['selected'];        

        foreach($class_ids as $value){
            $class_id = (int) $value;            
            $add_stmt->execute();
        }
        $need_reload = TRUE;
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
    <title>Class Registration</title>
    <?php
        if($_COOKIE[$mode] == $light){
            ?><link rel="stylesheet" href="css/basic.css"><?php
        }elseif($_COOKIE[$mode] == $dark){
            ?><link rel="stylesheet" href="css/darkmode.css"><?php
        }
    ?>
</head>
<body>
    <a href="home_page.php">Back to Home</a>
    <h1>Class Registration</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form>
    
    <!-- more html -->  
    <?php
        if(array_key_exists('terms_index', $_GET)){
            $term_start_date = $terms_result_both[$_GET['terms_index']]['term_start_date'];
            $term_end_date = $terms_result_both[$_GET['terms_index']]['term_end_date'];
            $term = $term_start_date . ' - ' . $term_end_date;

            //get all classes in term
            $classes_query = 'SELECT * FROM classes_view WHERE term = "' . $term . '"';
            $classes_select_stmt = $conn->prepare($classes_query);
            $classes_select_stmt->execute();
            $classes_result = $classes_select_stmt->get_result();
            $classes_result_both = $classes_result->fetch_all(MYSQLI_BOTH);

            //get all student's classes in term
            $student_class_history_query = file_get_contents($queries_dir . 'student_class_history_select_by_term.sql');
            $student_class_history_select_stmt = $conn->prepare($student_class_history_query);
            $student_class_history_select_stmt->bind_param('is', $student_id, $term);
            $student_class_history_select_stmt->execute();
            $student_class_history_result = $student_class_history_select_stmt->get_result();
            $student_class_history_result_both = $student_class_history_result->fetch_all(MYSQLI_BOTH);

            result_to_html_table_with_add_checkbox($classes_result_both, 'Add?', 'selected[]', 'class_id', 'Add Records', 'add_records');

            $student_class_history_select_stmt->execute();
            $student_class_history_result = $student_class_history_select_stmt->get_result();
            //result_to_html_table_with_del_checkbox($student_class_history_result); 
            result_to_html_table_with_add_checkbox($student_class_history_result_both, 'Delete?', 'selected[]', 'class_id', 'Delete Records', 'delete_records');



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

                <input type="submit" value="Submit">
            </form>

        <?php }
    ?>
    

    <?php $conn->close(); ?>
</body>
</html>
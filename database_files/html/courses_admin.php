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
    $select_query = "SELECT * FROM courses_view";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    if(array_key_exists('add_records', $_POST)){
        $course_discipline = $_POST['course_discipline'];
        $course_number = (int) $_POST['course_number'];
        $course_name = $_POST['course_name'];
        $course_credits = (int) $_POST['course_credits'];
        $course_description = $_POST['course_description'];

        $add_query = file_get_contents($queries_dir . 'courses_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('sisis', $course_discipline, $course_number, $course_name, $course_credits, $course_description);
        $add_stmt->execute();

        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    $need_reload = FALSE;

    if(array_key_exists('delbtn', $_POST)){

        $del_query = file_get_contents($queries_dir . "courses_delete.sql");
        $del_stmt = $conn->prepare($del_query);

        foreach($result_both as $row){
            $id = $row['course_id'];
            if(array_key_exists('checkbox' . $id, $_POST)){
                $need_reload = TRUE;
                $del_stmt->bind_param('i', $id);
                $del_stmt->execute();
            }
        }
    }

    // ----- Reload this page if the database was changed.
    if($need_reload){ // This needs to be done before any output, to guarantee that it works.
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if (array_key_exists('edit_records', $_POST)) {
        // Sanitize and collect form data
        $course_id = (int) $_POST["course_id"];
        $course_discipline = $_POST["course_discipline"];
        $course_number = (int) $_POST["course_number"];
        $course_name = $_POST["course_name"];
        $course_credits = (int) $_POST["course_credits"];
        $course_description = $_POST["course_description"];
    
        // Prepare the SQL query
        $edit_query = file_get_contents($queries_dir . 'courses_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
    
        // Bind the parameters for the prepared statement
        $edit_stmt->bind_param('sisisi', $course_discipline, $course_number, $course_name, $course_credits, $course_description, $course_id);
    
        // Execute the statement
        $edit_stmt->execute();
    
        // Redirect after successful update
        $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $redirect_url", true, 303);
        exit();
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
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
    <h1>Courses</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form>
    
    <!-- more html -->  
    <h2>Add Courses</h2>
    <form method="post">
        <label for="course_discipline">Course Discipline</label>
        <input type="text" name="course_discipline" id="course_discipline" required>

        <label for="course_number">Course Number</label>
        <input type="number" name="course_number" id="course_number" required>

        <label for="course_name">Course Name</label>
        <input type="text" name="course_name" id="course_name" required>

        <label for="course_credits">Course Credits</label>
        <input type="number" name="course_credits" id="course_credits" required>

        <label for="course_description">Course Description</label>
        <input type="text" name="course_description" id="course_description" required>

        <input type="submit" name="add_records" value="add_records">
    </form>

    <h2>Delete Courses</h2>
    <?php
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        result_to_html_table_with_del_checkbox($result);
    ?>

    <h2>Edit Courses</h2>
    <?php
        if(array_key_exists('edit_records', $_GET)){
            //generate_select_fields($conn, ["course_discipline", "course_number", "course_name", "course_credits", "course_description"], $result_both[$row_index]);
            $row_index = $_GET['selected_record'];
            $original_record = $result_both[$row_index];
            ?>
            <form method="post">
                <label for="course_discipline">Course Discipline</label>
                <input type="text" name="course_discipline" id="course_discipline" value="<?php echo $original_record['course_discipline']; ?>" required>
            
                <label for="course_number">Course Number</label>
                <input type="number" name="course_number" id="course_number" value="<?php echo $original_record['course_number']; ?>" required>
            
                <label for="course_name">Course Name</label>
                <input type="text" name="course_name" id="course_name" value="<?php echo $original_record['course_name']; ?>" required>
            
                <label for="course_credits">Course Credits</label>
                <input type="number" name="course_credits" id="course_credits" value="<?php echo $original_record['course_credits']; ?>" required>
            
                <label for="course_description">Course Description</label>
                <input type="text" name="course_description" id="course_description" value="<?php echo $original_record['course_description']; ?>" required>

                <input type="hidden" name="course_id" value="<?php echo $original_record['course_id']; ?>">
            
                <input type="submit" name="edit_records" value="Edit Records">
            </form>
            <?php
        }else{
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            generate_edit_selections($result);
        }
    ?>

    

    <?php $conn->close(); ?>
</body>
</html>
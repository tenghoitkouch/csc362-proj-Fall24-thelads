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

   

    $select_query = "SELECT * FROM course_prerequisites_view";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    $query_courses = "SELECT * FROM courses_view";
    $select_stmt_courses = $conn->prepare($query_courses);
    $select_stmt_courses->execute();
    $courses_result = $select_stmt_courses->get_result();
    $courses_list = $courses_result->fetch_all(MYSQLI_ASSOC);

    if(array_key_exists('add_records', $_POST)){
        $course_id = (int) $_POST['course_id'];
        $prerequisite_id = (int) $_POST['prerequisite_id'];

        $add_query = file_get_contents($queries_dir . 'course_prerequisites_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('ii', course_id, prerequisite_id);
        $add_stmt->execute();

        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
    $need_reload = FALSE;

    if(array_key_exists('delete_records', $_POST)){

        $del_query = file_get_contents($queries_dir . "course_prerequisites_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('i', $course_id);

        $course_ids = $_POST['selected'];  

        foreach($course_ids as $value){
            $course_id = (int) $value;            
            $del_stmt->execute();
        }
        $need_reload = TRUE;
    }

    if($need_reload){ 
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }


    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Prerequisites</title>
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
        <h2>Course Prerequisites</h2>
        <?php
        //delete
        result_to_html_table_with_checkbox_edit($result_both, 'Delete?', 'selected[]', 'course_id', 'Delete Course Prerequisites', 'delete_records');

        //add
        if(array_key_exists('add_records', $_POST)){
            echo '<h3>Add Course Prerequisite</h3>';
            $row_index = $_POST['add_records'];
            $original_record = $result_both[$row_index];
        }?>
        <h3>Add Course Prerequisite</h3>
        <form method="post">
        <label for="course_id">Course</label>
            <select name="course_id" id="course_id" required>
                <option value="" selected disabled>Select a course</option>
                <?php foreach ($courses_list as $course) : ?>
                    <option value="<?= $course['course_id'] ?>"><?= $course['course_discipline'] . ' ' . $course['course_number'] ?></option>
                <?php endforeach; ?>
            </select>
        <br>
        <label for="course_id">Prerequisite</label>
            <select name="prerequisite_id" id="course_id" required>
                <option value="" selected disabled>Select a course</option>
                <?php foreach ($courses_list as $course) : ?>
                    <option value="<?= $course['course_id'] ?>"><?= $course['course_discipline'] . ' ' . $course['course_number'] ?></option>
                <?php endforeach; ?>
            </select>
        <br>
        <button type="submit" name="add_records">Submit</button>
    </main>    
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
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
    $select_query = "SELECT * FROM students";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    $professors_select_query = "SELECT * FROM professors";
    $professors_select_stmt = $conn->prepare($professors_select_query);
    $professors_select_stmt->execute();
    $professors_result = $professors_select_stmt->get_result();
    $professors_result_both = $professors_result->fetch_all(MYSQLI_BOTH);

    if (array_key_exists('add_records', $_POST)) {
        $student_first_name = $_POST['student_first_name'];
        $student_last_name = $_POST['student_last_name'];
        $student_email = $_POST['student_email'];
        $student_phone_number = $_POST['student_phone_number'];
        $student_street = $_POST['student_street'];
        $student_city = $_POST['student_city'];
        $student_state = $_POST['student_state'];
        $student_zip_code = $_POST['student_zip_code'];
        $professor_id = $_POST['professor_id'];
    
        $add_query = file_get_contents($queries_dir . 'students_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param(
            'ssssssssi', 
            $student_first_name, 
            $student_last_name, 
            $student_email, 
            $student_phone_number, 
            $student_street, 
            $student_city, 
            $student_state, 
            $student_zip_code,
            $professor_id
        );
        $add_stmt->execute();
    
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
    
    $need_reload = FALSE;

    if (array_key_exists('delete_records', $_POST)) {
        // Load the delete query
        $del_query = file_get_contents($queries_dir . "students_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('i', $student_id); // Bind a single student_id
    
        // Get the list of student_ids to delete
        $student_ids = $_POST['selected'];
    
        // Loop through the IDs and execute the delete statement
        foreach ($student_ids as $value) {
            $student_id = (int) $value; // Cast to int for safety
            $del_stmt->execute();
        }
    
        // Optional: flag to trigger a reload or other post-delete logic
        $need_reload = TRUE;
    }
    
    if($need_reload){ // This needs to be done before any output, to guarantee that it works.
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if (array_key_exists('complete_edit_records', $_POST)) {
        $student_id = (int) $_POST['student_id']; // Primary key to identify the student
        $student_first_name = $_POST['student_first_name'];
        $student_last_name = $_POST['student_last_name'];
        $student_email = $_POST['student_email'];
        $student_phone_number = $_POST['student_phone_number'];
        $student_street = $_POST['student_street'];
        $student_city = $_POST['student_city'];
        $student_state = $_POST['student_state'];
        $student_zip_code = $_POST['student_zip_code'];
        $professor_id = $_POST['professor_id'];
    
        // Load the update query
        $edit_query = file_get_contents($queries_dir . 'students_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
    
        // Bind parameters
        $edit_stmt->bind_param(
            'sssssssiis', 
            $student_first_name, 
            $student_last_name, 
            $student_email, 
            $student_phone_number, 
            $student_street, 
            $student_city, 
            $student_state, 
            $student_zip_code,
            $professor_id,
            $student_id
        );
    
        // Execute the query
        $edit_stmt->execute();
    
        // Refresh the page
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
    <title>Students</title>
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
    <h1>Students</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form>
    
    <!-- more html -->  
     <h2>Add Student</h2>
     <form method="POST">
        <!-- First Name -->
        <label for="student_first_name">First Name:</label>
        <input type="text" id="student_first_name" name="student_first_name" required>
        <br>

        <!-- Last Name -->
        <label for="student_last_name">Last Name:</label>
        <input type="text" id="student_last_name" name="student_last_name" required>
        <br>

        <!-- Email -->
        <label for="student_email">Email:</label>
        <input type="email" id="student_email" name="student_email" required>
        <br>

        <!-- Phone Number -->
        <label for="student_phone_number">Phone Number:</label>
        <input type="tel" id="student_phone_number" name="student_phone_number">
        <br>

        <!-- Street Address -->
        <label for="student_street">Street Address:</label>
        <input type="text" id="student_street" name="student_street">
        <br>

        <!-- City -->
        <label for="student_city">City:</label>
        <input type="text" id="student_city" name="student_city">
        <br>

        <!-- State -->
        <label for="student_state">State:</label>
        <input type="text" id="student_state" name="student_state">
        <br>

        <!-- ZIP Code -->
        <label for="student_zip_code">ZIP Code:</label>
        <input type="text" id="student_zip_code" name="student_zip_code">
        <br>

        <!-- Professor -->
        <label for="professor_id">Professor:</label>
        <select id="professor_id" name="professor_id" required>
            <?php
                foreach ($professors_result_both as $professor) {
                    echo '<option value="' . $professor['professor_id'] . '">' . $professor['professor_first_name'] . ' ' . $professor['professor_last_name'] . '</option>';
                }
            ?>
        </select>
        <br><br>

        <!-- Submit Button -->
        <button type="submit" name="add_records">Submit</button>
    </form>
    
    <h2>Delete Students</h2>
    <?php
        result_to_html_table_with_checkbox_edit($result_both, 'Delete?', 'selected[]', 'student_id', 'Delete Records', 'delete_records');
    ?>

    <?php
        if(array_key_exists('edit_records', $_POST)){
            echo '<h2>Edit Student</h2>';
            $row_index = $_POST['edit_records'];
            $original_record = $result_both[$row_index];

            ?>
            <form method="POST">
                <label for="student_id">Student ID</label>
                <input type="text" name="student_id" id="student_id" value="<?php echo $original_record['student_id']; ?>" readonly>
                <!-- First Name -->
                <label for="student_first_name">First Name:</label>
                <input type="text" id="student_first_name" name="student_first_name" value="<?php echo $original_record['student_first_name']; ?>" required>

                <!-- Last Name -->
                <label for="student_last_name">Last Name:</label>
                <input type="text" id="student_last_name" name="student_last_name" value="<?php echo $original_record['student_last_name']; ?>" required>

                <!-- Email -->
                <label for="student_email">Email:</label>
                <input type="email" id="student_email" name="student_email" value="<?php echo $original_record['student_email']; ?>" required>

                <!-- Phone Number -->
                <label for="student_phone_number">Phone Number:</label>
                <input type="tel" id="student_phone_number" name="student_phone_number" value="<?php echo $original_record['student_phone_number']; ?>">

                <!-- Street Address -->
                <label for="student_street">Street Address:</label>
                <input type="text" id="student_street" name="student_street" value="<?php echo $original_record['student_street']; ?>">

                <!-- City -->
                <label for="student_city">City:</label>
                <input type="text" id="student_city" name="student_city" value="<?php echo $original_record['student_city']; ?>">

                <!-- State -->
                <label for="student_state">State:</label>
                <input type="text" id="student_state" name="student_state" value="<?php echo $original_record['student_state']; ?>">

                <!-- ZIP Code -->
                <label for="student_zip_code">ZIP Code:</label>
                <input type="text" id="student_zip_code" name="student_zip_code" value="<?php echo $original_record['student_zip_code']; ?>">

                <!-- Professor -->
                <label for="professor_id">Professor:</label>
                <select id="professor_id" name="professor_id" required>
                    <?php
                        foreach ($professors_result_both as $professor) {
                            $selected = ($professor['professor_id'] == $original_record['professor_id']) ? 'selected' : '';
                            echo '<option value="' . $professor['professor_id'] . '" ' . $selected . '>' . $professor['professor_first_name'] . ' ' . $professor['professor_last_name'] . '</option>';
                        }
                    ?>
                </select>

                <!-- Submit Button -->
                <button type="submit" name="complete_edit_records">Submit</button>

            </form>

            <?php
        }

    ?>
    

    <?php $conn->close(); ?>
</body>
</html>

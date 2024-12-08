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
    $select_query = "SELECT * FROM professors";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    if (array_key_exists('add_records', $_POST)) {
        $professor_first_name = $_POST['professor_first_name'];
        $professor_last_name = $_POST['professor_last_name'];
        $professor_email = $_POST['professor_email'];
        $professor_phone_number = $_POST['professor_phone_number'];
        $professor_street = $_POST['professor_street'];
        $professor_city = $_POST['professor_city'];
        $professor_state = $_POST['professor_state'];
        $professor_zip_code = $_POST['professor_zip_code'];
    
        $add_query = file_get_contents($queries_dir . 'professors_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param(
            'ssssssss', 
            $professor_first_name, 
            $professor_last_name, 
            $professor_email, 
            $professor_phone_number, 
            $professor_street, 
            $professor_city, 
            $professor_state, 
            $professor_zip_code
        );
        $add_stmt->execute();
    
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
    
    $need_reload = FALSE;

    if (array_key_exists('delete_records', $_POST)) {
        // Load the delete query
        $del_query = file_get_contents($queries_dir . "professors_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('i', $professor_id); // Bind a single professor_id
    
        // Get the list of professor_ids to delete
        $professor_ids = $_POST['selected'];
    
        // Loop through the IDs and execute the delete statement
        foreach ($professor_ids as $value) {
            $professor_id = (int) $value; // Cast to int for safety
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
        $professor_id = (int) $_POST['professor_id']; // Primary key to identify the professor
        $professor_first_name = $_POST['professor_first_name'];
        $professor_last_name = $_POST['professor_last_name'];
        $professor_email = $_POST['professor_email'];
        $professor_phone_number = $_POST['professor_phone_number'];
        $professor_street = $_POST['professor_street'];
        $professor_city = $_POST['professor_city'];
        $professor_state = $_POST['professor_state'];
        $professor_zip_code = $_POST['professor_zip_code'];
    
        // Load the update query
        $edit_query = file_get_contents($queries_dir . 'professors_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
    
        // Bind parameters
        $edit_stmt->bind_param(
            'ssssssssi', 
            $professor_first_name, 
            $professor_last_name, 
            $professor_email, 
            $professor_phone_number, 
            $professor_street, 
            $professor_city, 
            $professor_state, 
            $professor_zip_code, 
            $professor_id
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
    <title>Professors</title>
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
    <h1>Professors</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form>
    
    <!-- more html -->  
     <h2>Add Professors</h2>
     <form method="POST">
        <!-- First Name -->
        <label for="professor_first_name">First Name:</label>
        <input type="text" id="professor_first_name" name="professor_first_name" required>
        <br><br>

        <!-- Last Name -->
        <label for="professor_last_name">Last Name:</label>
        <input type="text" id="professor_last_name" name="professor_last_name" required>
        <br><br>

        <!-- Email -->
        <label for="professor_email">Email:</label>
        <input type="email" id="professor_email" name="professor_email" required>
        <br><br>

        <!-- Phone Number -->
        <label for="professor_phone_number">Phone Number:</label>
        <input type="tel" id="professor_phone_number" name="professor_phone_number">
        <br><br>

        <!-- Street Address -->
        <label for="professor_street">Street Address:</label>
        <input type="text" id="professor_street" name="professor_street">
        <br><br>

        <!-- City -->
        <label for="professor_city">City:</label>
        <input type="text" id="professor_city" name="professor_city">
        <br><br>

        <!-- State -->
        <label for="professor_state">State:</label>
        <input type="text" id="professor_state" name="professor_state">
        <br><br>

        <!-- ZIP Code -->
        <label for="professor_zip_code">ZIP Code:</label>
        <input type="text" id="professor_zip_code" name="professor_zip_code">
        <br><br>

        <!-- Submit Button -->
        <button type="submit" name="add_records">Submit</button>
    </form>
    
    <h2>Delete Professors</h2>
    <?php
        result_to_html_table_with_checkbox_edit($result_both, 'Delete?', 'selected[]', 'professor_id', 'Delete Records', 'delete_records');
    ?>

    <?php

        if(array_key_exists('edit_records', $_POST)){
            echo '<h2>Edit Professor</h2>';
            $row_index = $_POST['edit_records'];
            $original_record = $result_both[$row_index];

            ?>
            <form method="POST">
                <label for="professor_id">Professor ID</label>
                <input type="text" name="professor_id" id="professor_id" value="<?php echo $original_record['professor_id']; ?>" readonly>
                <!-- First Name -->
                <label for="professor_first_name">First Name:</label>
                <input type="text" id="professor_first_name" name="professor_first_name" value="<?php echo $original_record['professor_first_name']; ?>" required>

                <!-- Last Name -->
                <label for="professor_last_name">Last Name:</label>
                <input type="text" id="professor_last_name" name="professor_last_name" value="<?php echo $original_record['professor_last_name']; ?>" required>

                <!-- Email -->
                <label for="professor_email">Email:</label>
                <input type="email" id="professor_email" name="professor_email" value="<?php echo $original_record['professor_email']; ?>" required>

                <!-- Phone Number -->
                <label for="professor_phone_number">Phone Number:</label>
                <input type="tel" id="professor_phone_number" name="professor_phone_number" value="<?php echo $original_record['professor_phone_number']; ?>">

                <!-- Street Address -->
                <label for="professor_street">Street Address:</label>
                <input type="text" id="professor_street" name="professor_street" value="<?php echo $original_record['professor_street']; ?>">

                <!-- City -->
                <label for="professor_city">City:</label>
                <input type="text" id="professor_city" name="professor_city" value="<?php echo $original_record['professor_city']; ?>">

                <!-- State -->
                <label for="professor_state">State:</label>
                <input type="text" id="professor_state" name="professor_state" value="<?php echo $original_record['professor_state']; ?>">

                <!-- ZIP Code -->
                <label for="professor_zip_code">ZIP Code:</label>
                <input type="text" id="professor_zip_code" name="professor_zip_code" value="<?php echo $original_record['professor_zip_code']; ?>">

                <!-- Submit Button -->
                <button type="submit" name="complete_edit_records">Submit</button>

            </form>

            <?php
        }

    ?>
    

    <?php $conn->close(); ?>
</body>
</html>
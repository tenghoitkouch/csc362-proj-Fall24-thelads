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

    $select_query = "SELECT * FROM locations_view";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    $select_query = "SELECT * FROM buildings_view";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    if(array_key_exists('add_records', $_POST)){
        $room_number = (int) $_POST['room_number'];
        $room_capacity = (int) $_POST['room_capacity'];
        $building_name = $_POST['building_name'];

        $add_query = file_get_contents($queries_dir . 'locations_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('sii', building_name, room_number, room_capacity);
        $add_stmt->execute();

        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
    $need_reload = FALSE;

    if(array_key_exists('delete_records', $_POST)){

        $del_query = file_get_contents($queries_dir . "locations_delete.sql");
        $del_stmt = $conn->prepare($del_query);

        $selected_rooms = $_POST['selected'];
    
        foreach($selected_rooms as $room){
            
            $building_name = $room['building_name'];
            $room_number = $room['room_number'];
            $del_stmt->bind_param('si', $building_name, $room_number); 
            $del_stmt->execute();
        }
    
        
        $need_reload = TRUE;
    }

    if($need_reload){
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(array_key_exists('complete_edit_records', $_POST)){
        $room_number = (int) $_POST['room_number'];
        $room_capacity = (int) $_POST['room_capacity'];
        $building_name = $_POST['building_name'];

        $edit_query = file_get_contents($queries_dir . 'locations_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
        $edit_stmt->bind_param('sii', building_name, room_number, room_capacity );
        $edit_stmt->execute();
        
        //refresh
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
        <h2>Locations</h2>
        <?php
        result_to_html_table_with_checkbox_edit($result_both, 'Delete?', 'selected[]', 'course_id', 'Delete Courses', 'delete_records');
        if(array_key_exists('edit_records', $_POST)){
            echo '<h3>Edit Course</h3>';
            $row_index = $_POST['edit_records'];
            $original_record = $result_both[$row_index];
            ?>
            <form method="post">
            <label for="building_name">Building</label>
            <select name="building_name" id="building_name" required>
                <option value="" selected disabled>Select a building</option>
                <?php foreach ($buildings_list as $building) : ?>
                    <option value="<?= $building['building_name'] ?>"></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="room_number">Room Number</label>
                <input type="number" name="room_number" id="room_number" value="<?php echo $original_record['room_number']; ?>" required>
            <br>
            <label for="room_capacity">Room Capacity</label>
                <input type="number" name="room_capacity" id="room_capacity" value="<?php echo $original_record['room_capacity']; ?>" required>
            <br>
            <button type="submit" name="complete_edit_records">Submit</button>
            </form>
            <?php
        }
        

        if(array_key_exists('add_records', $_POST)){
            echo '<h3>Add Location</h3>';
            $row_index = $_POST['add_records'];
            $original_record = $result_both[$row_index];
        }?>
        <h3>Add Location</h3>
        <form method="post">
            <label for="building_name">Building</label>
            <select name="building_name" id="building_name" required>
                <option value="" selected disabled>Select a building</option>
                <?php foreach ($buildings_list as $building) : ?>
                    <option value="<?= $building['building_name'] ?>"></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="room_number">Room Number</label>
                <input type="number" name="room_number" id="room_number" value="<?php echo $original_record['room_number']; ?>" required>
            <br>
            <label for="room_capacity">Room Capacity</label>
                <input type="number" name="room_capacity" id="room_capacity" value="<?php echo $original_record['room_capacity']; ?>" required>
            <br>
            <button type="submit" name="complete_edit_records">Submit</button>
        </form>

    </main>    
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
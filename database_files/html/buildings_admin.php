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

    $select_query = "SELECT * FROM buildings_view";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $result_both = $result->fetch_all(MYSQLI_BOTH);

    if(array_key_exists('add_records', $_POST)){
        $building_name = $_POST['building_name'];

        $add_query = file_get_contents($queries_dir . 'buildings_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('s', building_name);
        $add_stmt->execute();

        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
    $need_reload = FALSE;

    if(array_key_exists('delete_records', $_POST)){

        $del_query = file_get_contents($queries_dir . "buildings_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('s', $building_name);

        $building_names = $_POST['selected'];  

        foreach($building_names as $value){
            $building_name =  $value;            
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
        <h2>Buildings</h2>
        <?php
        //delete
        result_to_html_table_with_checkbox_edit($result_both, 'Delete?', 'selected[]', 'building_name', 'Delete Buildings', 'delete_records');

        if(array_key_exists('add_records', $_POST)){
            echo '<h3>Add Building</h3>';
            $row_index = $_POST['add_records'];
            $original_record = $result_both[$row_index];
        }?>
        <h3>Add Building</h3>
        <form method="post">
            <label for="buildng_name">Building Name</label>
                <input type="text" name="building_name" id="building_name" value="<?php echo $original_record['building_name']; ?>" required>
            <br>
            <button type="submit" name="complete_edit_records">Submit</button>
        </form>
    </main>    
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>
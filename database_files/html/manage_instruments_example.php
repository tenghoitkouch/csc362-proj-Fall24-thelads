<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  

    function result_to_html_table($result) {
        $qryres = $result->fetch_all();
        $n_rows = $result->num_rows;
        $n_cols = $result->field_count;
        $fields = $result->fetch_fields();
        ?>
        <!-- Description of table - - - - - - - - - - - - - - - - - - - - -->
        <!-- <p>This table has <?php //echo $n_rows; ?> and <?php //echo $n_cols; ?> columns.</p> -->
        
        <!-- Begin header - - - - - - - - - - - - - - - - - - - - -->
        <!-- Using default action (this page). -->
         <form method="POST">
            <table>
                <thead>
                    <tr>
                        <td>Delete?</td>
                        <?php for ($i=0; $i<$n_cols; $i++){ ?>
                            <td><b><?php echo $fields[$i]->name; ?></b></td>
                        <?php } ?>
                    </tr>
                </thead>
            
            <!-- Begin body - - - - - - - - - - - - - - - - - - - - - -->
                <tbody>
                    <?php for ($i=0; $i<$n_rows; $i++){ ?>
                        <?php $id = $qryres[$i][0]; ?>
                            <tr>
                                <td><input type="checkbox" name="checkbox<?php echo $id; ?>" value="<?php echo $id; ?>" <?php if(isset($qryres[$i][2])){ echo "disabled"; } ?>/></td>
                                <?php for($j=0; $j<$n_cols; $j++){ ?>
                                    <td><?php echo $qryres[$i][$j]; ?></td>
                                <?php } ?>
                            </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p><input type="submit" name="delbtn" value="Delete Selected Records" /></p>
        </form><?php } ?>

<?php
    $config = parse_ini_file('../../mysql.ini');
    $sql_location = "";
    $dbname = 'instrument_rentals';
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

    require("library.php");

    // ---- TOGGLE MODE -------------------------------------

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

    // ---- SESSION START ----------------------------------------

    session_start();

    if(!array_key_exists('num_deleted', $_SESSION)){
        $_SESSION['num_deleted'] = 0;
    }

    if(array_key_exists('logout', $_POST)){
        session_unset();
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(isset($_POST['username'])){
        $_SESSION['username'] = $_POST['username'];
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    //add recs
    if(array_key_exists('add_records', $_POST)){

        $add_tbl = file_get_contents($sql_location . 'add_instruments.sql');
        // $add_stmt = $conn->prepare($add_tbl);
        // $add_stmt->execute();
        if(!$conn->query($add_tbl)){
            echo $conn->error;
            echo "Failed to insert records!\n";
        } else {
            header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
            exit(); // In this case, we are GETting the page we currently on.
        }
    }

    //select records
    $sel_stmt = file_get_contents($sql_location . "select_instruments.sql");
    $result = $conn->query($sel_stmt);
    $num_fields = $result->field_count;
    $num_records = $result->num_rows;
    $instrument_list = $result->fetch_all();

    $need_reload = FALSE;
    //del rec
    if(array_key_exists('delbtn', $_POST)){

        $del_str = file_get_contents('/home/tenghoitkouch/csc362_f24_kouch/html/delete_instrument.sql');
        $del_stmt = $conn->prepare($del_str);
        $del_stmt->bind_param('i', $id);

        // $get_all_instrument_ids = "SELECT instrument_id FROM instruments";
        // $idlist = $conn->query($get_all_instrument_ids);

        for($i = 0; $i < $result->num_rows; $i++){
            $id = $instrument_list[$i][0];
            if(array_key_exists('checkbox' . $id, $_POST)){
                $need_reload = TRUE;
                $del_stmt->execute();
                if(session_status() == PHP_SESSION_ACTIVE){
                    $_SESSION['num_deleted'] = $_SESSION['num_deleted'] + 1;
                }
            }
        }
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
    <title>Instrument Rentals</title>

    <?php
        if($_COOKIE[$mode] == $light){
            ?><link rel="stylesheet" href="css/basic.css"><?php
        }elseif($_COOKIE[$mode] == $dark){
            ?><link rel="stylesheet" href="css/darkmode.css"><?php
        }
    ?>

</head>
<body>
    <h1>Manage Instruments</h1>
    <form method="post">
        <p><input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode" /></p>
    </form>

    <?php session_display(); ?>
    
    <h2>Metadata:</h2>
    <p><?php echo $num_fields; ?> field(s) in results.</p>
    <p><?php echo $num_records; ?> row(s) in results.</p>
    
    <h2>Table:</h2>
    <?php 
        $result = $conn->query($sel_stmt);
        result_to_html_table($result);
    ?>

    <?php
        if(session_status() == PHP_SESSION_ACTIVE){
            ?> <p>You have deleted <?php echo $_SESSION['num_deleted']; ?> record(s) in this session.</p> <?php
        }
    ?>
    
    <form method="POST">
        <input type="submit" name="add_records" value="Add extra records" />  
    </form>

</body>
</html>
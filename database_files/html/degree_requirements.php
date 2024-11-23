<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection
$config = parse_ini_file('../../../mysql.ini');
$dbname = 'ku_registrar';
$conn = new mysqli(
    $config['mysqli.default_host'],
    $config['mysqli.default_user'],
    $config['mysqli.default_pw'],
    $dbname
);

if ($conn->connect_errno) {
    die("Error: Failed to connect to the database: " . $conn->connect_error);
}

// helper functions
require "library.php";

//  mode toggle
$mode = 'mode';
$light = 'light';
$dark = 'dark';
if (!array_key_exists($mode, $_COOKIE)) {
    setcookie($mode, $light, 0, "/", "", false, true);
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}
if (array_key_exists('toggle_mode', $_POST)) {
    $new_mode = $_COOKIE[$mode] == $light ? $dark : $light;
    setcookie($mode, $new_mode, 0, "/", "", false, true);
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}

// Start session handling
session_start();
if (array_key_exists('logout', $_POST)) {
    session_unset();
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}
if (isset($_POST['username'])) {
    $_SESSION['username'] = $_POST['username'];
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $degree_id = $_POST['degree_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;

    if (isset($_POST['action']) && $degree_id && $course_id) {
        switch ($_POST['action']) {
            case 'add':
                $query = "INSERT INTO degree_requirements (degree_id, course_id) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $degree_id, $course_id);
                $stmt->execute();
                break;
            case 'delete':
                $query = "DELETE FROM degree_requirements WHERE degree_id = ? AND course_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $degree_id, $course_id);
                $stmt->execute();
                break;
        }
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
}

// Fetch degrees, courses, and current requirements
$degrees = $conn->query("SELECT degree_id, degree_name FROM degrees");
$courses = $conn->query("SELECT course_id, course_discipline, course_number FROM courses");
$requirements = $conn->query("
    SELECT dr.degree_id, d.degree_name, dr.course_id, c.course_discipline, c.course_number
    FROM degree_requirements dr
    JOIN degrees d ON dr.degree_id = d.degree_id
    JOIN courses c ON dr.course_id = c.course_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Degree Requirements</title>
    <?php if ($_COOKIE[$mode] == $light) { ?>
        <link rel="stylesheet" href="../css/basic.css">
    <?php } else { ?>
        <link rel="stylesheet" href="../css/darkmode.css">
    <?php } ?>
</head>
<body>
    <h1>Manage Degree Requirements</h1>
    <form method="post">
        <input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode">
    </form>

    <?php
        if(isset($_SESSION['username'])){
            ?><p>Welocome <?php echo $_SESSION['username']; ?></p>
            <form method="POST">
                <input type="submit" name="logout" value="Logout">
            </form><?php
        }else{
            ?><p>Enter name to start/resume session: </p>
            <form method="POST">
                <input type="text" name="username" placeholder="Enter name...">
                <input type="submit" value="Remember Me">
            </form><?php 
        }
    ?>

    <h2>Add or Remove Degree Requirements</h2>
    <form method="POST">
        <label>Degree:</label>
        <select name="degree_id" required>
            <?php while ($row = $degrees->fetch_assoc()) { ?>
                <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
            <?php } ?>
        </select>

        <label>Course:</label>
        <select name="course_id" required>
            <?php while ($row = $courses->fetch_assoc()) { ?>
                <option value="<?= $row['course_id'] ?>"><?= $row['course_discipline'] ?> - <?= $row['course_number'] ?></option>
            <?php } ?>
        </select>

        <button type="submit" name="action" value="add">Add Requirement</button>
        <button type="submit" name="action" value="delete">Delete Requirement</button>
    </form>

    <h2>Current Degree Requirements</h2>
    <?php result_to_html_table($requirements); ?>

    <?php $conn->close(); ?>
</body>
</html>

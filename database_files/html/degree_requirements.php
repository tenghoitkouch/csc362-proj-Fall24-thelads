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
session_start();

if(array_key_exists('logout', $_POST)){
    session_unset();
    $_SESSION['logged_in'] = FALSE;

    header("Location: home.php", true, 303);
    exit;
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
        <h2>Degree Requirements</h2>
        <!-- <h2>Add or Remove Degree Requirements</h2>
        <form method="POST">
            <label>Degree:</label>
            <select name="degree_id" required>
                <?php //while ($row = $degrees->fetch_assoc()) { ?>
                    <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
                <?php //} ?>
            </select>

            <label>Course:</label>
            <select name="course_id" required>
                <?php //while ($row = $courses->fetch_assoc()) { ?>
                    <option value="<?= $row['course_id'] ?>"><?= $row['course_discipline'] ?> - <?= $row['course_number'] ?></option>
                <?php //} ?>
            </select>

            <button type="submit" name="action" value="add">Add Requirement</button>
            <button type="submit" name="action" value="delete">Delete Requirement</button>
        </form>

        <h2>Current Degree Requirements</h2> -->
        <?php result_to_html_table($requirements); ?>
    </main>
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>

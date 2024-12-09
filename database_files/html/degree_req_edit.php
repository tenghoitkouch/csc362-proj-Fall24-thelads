<?php
// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MySQLi error reporting
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

// Import custom PHP functions
require "library.php";
session_start();

if (array_key_exists('logout', $_POST)) {
    session_unset();
    $_SESSION['logged_in'] = FALSE;
    header("Location: home.php", true, 303);
    exit;
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $degree_id = $_POST['degree_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;

    if (isset($_POST['action'])) {
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
            case 'update':
                $old_course_id = $_POST['old_course_id'];
                $new_course_id = $_POST['new_course_id'];
                $query = "UPDATE degree_requirements SET course_id = ? WHERE degree_id = ? AND course_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iii", $new_course_id, $degree_id, $old_course_id);
                $stmt->execute();
                break;
        }
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
}

// Fetch data for forms
$degrees = $conn->query("SELECT degree_id, degree_name FROM degrees")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT course_id, course_discipline, course_number FROM courses")->fetch_all(MYSQLI_ASSOC);
$requirements = $conn->query("
    SELECT dr.degree_id, d.degree_name, dr.course_id, c.course_discipline, c.course_number
    FROM degree_requirements dr
    JOIN degrees d ON dr.degree_id = d.degree_id
    JOIN courses c ON dr.course_id = c.course_id
")->fetch_all(MYSQLI_ASSOC);

// Fetch degrees and courses currently associated in requirements
$existing_degrees = $conn->query("
    SELECT DISTINCT d.degree_id, d.degree_name
    FROM degree_requirements dr
    JOIN degrees d ON dr.degree_id = d.degree_id
")->fetch_all(MYSQLI_ASSOC);

$existing_courses = $conn->query("
    SELECT DISTINCT c.course_id, c.course_discipline, c.course_number
    FROM degree_requirements dr
    JOIN courses c ON dr.course_id = c.course_id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Degree Requirements Admin</title>
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
        <h2>Degree Requirements Admin</h2>

        <!-- Add Degree Requirement -->
        <h2>Add Requirement</h2>
        <form method="POST">
            <label>Degree:</label>
            <select name="degree_id" required>
                <?php foreach ($degrees as $row) { ?>
                    <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
                <?php } ?>
            </select>

            <label>Course:</label>
            <select name="course_id" required>
                <?php foreach ($courses as $row) { ?>
                    <option value="<?= $row['course_id'] ?>"><?= $row['course_discipline'] ?> - <?= $row['course_number'] ?></option>
                <?php } ?>
            </select>

            <button type="submit" name="action" value="add">Add Requirement</button>
        </form>

        <!-- Update Degree Requirement -->
        <h2>Update Requirement</h2>
        <form method="POST">
            <label>Degree:</label>
            <select name="degree_id" required>
                <?php foreach ($existing_degrees as $row) { ?>
                    <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
                <?php } ?>
            </select>

            <label>Old Course:</label>
            <select name="old_course_id" required>
                <?php foreach ($requirements as $requirement) { ?>
                    <option value="<?= $requirement['course_id'] ?>"><?= $requirement['course_discipline'] ?> - <?= $requirement['course_number'] ?></option>
                <?php } ?>
            </select>

            <label>New Course:</label>
            <select name="new_course_id" required>
                <?php foreach ($courses as $row) { ?>
                    <option value="<?= $row['course_id'] ?>"><?= $row['course_discipline'] ?> - <?= $row['course_number'] ?></option>
                <?php } ?>
            </select>

            <button type="submit" name="action" value="update">Update Requirement</button>
        </form>

        <!-- Delete Degree Requirement -->
        <h2>Delete Requirement</h2>
        <form method="POST">
            <label>Degree:</label>
            <select name="degree_id" required>
                <?php foreach ($existing_degrees as $row) { ?>
                    <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
                <?php } ?>
            </select>

            <label>Course:</label>
            <select name="course_id" required>
                <?php foreach ($requirements as $requirement) { ?>
                    <option value="<?= $requirement['course_id'] ?>"><?= $requirement['course_discipline'] ?> - <?= $requirement['course_number'] ?></option>
                <?php } ?>
            </select>

            <button type="submit" name="action" value="delete">Delete Requirement</button>
        </form>

        <!-- Display All Requirements -->
        <h2>All Requirements</h2>
        <table>
            <thead>
                <tr>
                    <th>Degree ID</th>
                    <th>Degree Name</th>
                    <th>Course ID</th>
                    <th>Course Discipline</th>
                    <th>Course Number</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $row) { ?>
                    <tr>
                        <td><?= $row['degree_id'] ?></td>
                        <td><?= $row['degree_name'] ?></td>
                        <td><?= $row['course_id'] ?></td>
                        <td><?= $row['course_discipline'] ?></td>
                        <td><?= $row['course_number'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>
</body>
</html>

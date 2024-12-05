<?php
//error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//MySQLi error reporting
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

// TOGGLE LIGHT/DARK MODE
$mode = 'mode';
$light = "light";
$dark = "dark";

if (!array_key_exists($mode, $_COOKIE)) {
    setcookie($mode, $light, 0, "/", "", false, true); // default to light mode
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}

if (array_key_exists("toggle_mode", $_POST)) {
    $new_mode = ($_COOKIE[$mode] == $light) ? $dark : $light;
    setcookie($mode, $new_mode, 0, "/", "", false, true);
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
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

// Fetch data
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
    <title>Degree Requirements Admin</title>
    <?php if ($_COOKIE[$mode] == $light) { ?>
        <link rel="stylesheet" href="css/basic.css">
    <?php } else { ?>
        <link rel="stylesheet" href="css/darkmode.css">
    <?php } ?>
</head>
<body>
    <h1>Degree Requirements Admin</h1>
    <form method="post">
        <input type="submit" name="toggle_mode" value="Toggle Light/Dark Mode">
    </form>

    <!-- Add Degree Requirement -->
    <h2>Add Requirement</h2>
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
    </form>

    <!-- Update Degree Requirement -->
    <h2>Update Requirement</h2>
    <form method="POST">
        <label>Degree:</label>
        <select name="degree_id" required>
            <?php while ($row = $degrees->fetch_assoc()) { ?>
                <option value="<?= $row['degree_id'] ?>"><?= $row['degree_name'] ?></option>
            <?php } ?>
        </select>

        <label>Old Course:</label>
        <select name="old_course_id" required>
            <?php while ($row = $courses->fetch_assoc()) { ?>
                <option value="<?= $row['course_id'] ?>"><?= $row['course_discipline'] ?> - <?= $row['course_number'] ?></option>
            <?php } ?>
        </select>

        <label>New Course:</label>
        <select name="new_course_id" required>
            <?php while ($row = $courses->fetch_assoc()) { ?>
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
            <?php while ($row = $requirements->fetch_assoc()) { ?>
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

    <?php $conn->close(); ?>
</body>
</html>

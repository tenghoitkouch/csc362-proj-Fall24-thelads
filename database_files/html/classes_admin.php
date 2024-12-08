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

    //add recs
    if(array_key_exists('add_records', $_POST)){

        $course_id = (int) $_POST["course_id"];
        $section = $_POST["section"];
        $term_id = (int) $_POST["term_id"];
        $professor_id = (int) $_POST["professor_id"];
        list($building_name, $room_number) = explode(',', $_POST["location"]);
        $room_number = (int) $room_number;
        $meeting_day_id = (int) $_POST["meeting_days_id"];
        list($time_start, $time_end) = explode(',', $_POST["meeting_times"]);
        $class_max_capacity = (int) $_POST['class_max_capacity'];

        //query
        $add_query = file_get_contents($queries_dir . 'classes_insert.sql');
        $add_stmt = $conn->prepare($add_query);
        $add_stmt->bind_param('isiisiissi', $course_id, $section, $term_id, $professor_id, $building_name, $room_number, $meeting_day_id, $time_start, $time_end, $class_max_capacity);
        $add_stmt->execute();
        
        //refresh
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    
    //more sql setups
    $query = "SELECT * FROM classes_view";
    $select_stmt = $conn->prepare($query);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $classes_list = $result->fetch_all(MYSQLI_BOTH);

    // Query for Courses
    $query_courses = "SELECT * FROM courses_view";
    $select_stmt_courses = $conn->prepare($query_courses);
    $select_stmt_courses->execute();
    $courses_result = $select_stmt_courses->get_result();
    $courses_list = $courses_result->fetch_all(MYSQLI_ASSOC);

    // Query for Professors
    $query_professors = "SELECT * FROM professors_view";
    $select_stmt_professors = $conn->prepare($query_professors);
    $select_stmt_professors->execute();
    $professors_result = $select_stmt_professors->get_result();
    $professors_list = $professors_result->fetch_all(MYSQLI_ASSOC);

    // Query for Locations
    $query_locations = "SELECT * FROM locations_view";
    $select_stmt_locations = $conn->prepare($query_locations);
    $select_stmt_locations->execute();
    $locations_result = $select_stmt_locations->get_result();
    $locations_list = $locations_result->fetch_all(MYSQLI_ASSOC);

    // Query for Meeting Times
    $query_meeting_times = "SELECT * FROM meeting_times_view";
    $select_stmt_meeting_times = $conn->prepare($query_meeting_times);
    $select_stmt_meeting_times->execute();
    $meeting_times_result = $select_stmt_meeting_times->get_result();
    $meeting_times_list = $meeting_times_result->fetch_all(MYSQLI_ASSOC);

    $query_meeting_days = "SELECT * FROM meeting_days_view";
    $select_stmt_meeting_days = $conn->prepare($query_meeting_days);
    $select_stmt_meeting_days->execute();
    $meeting_days_result = $select_stmt_meeting_days->get_result();
    $meeting_days_list = $meeting_days_result->fetch_all(MYSQLI_ASSOC);
    

    // Query for Terms
    $query_terms = "SELECT * FROM terms_view";
    $select_stmt_terms = $conn->prepare($query_terms);
    $select_stmt_terms->execute();
    $terms_result = $select_stmt_terms->get_result();
    $terms_list = $terms_result->fetch_all(MYSQLI_ASSOC);

    $need_reload = FALSE;
    //del rec
    if(array_key_exists('delbtn', $_POST)){

        $del_query = file_get_contents($queries_dir . "classes_delete.sql");
        $del_stmt = $conn->prepare($del_query);
        $del_stmt->bind_param('i', $id);

        // $get_all_instrument_ids = "SELECT instrument_id FROM instruments";
        // $idlist = $conn->query($get_all_instrument_ids);

        for($i = 0; $i < $result->num_rows; $i++){
            $id = $classes_list[$i][0];
            if(array_key_exists('checkbox' . $id, $_POST)){
                $need_reload = TRUE;
                $del_stmt->execute();
            }
        }
    }

    // ----- Reload this page if the database was changed.
    if($need_reload){ // This needs to be done before any output, to guarantee that it works.
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }

    if(array_key_exists('complete_edit_records', $_POST)){

        $course_id = (int) $_POST["course_id"];
        $section = $_POST["section"];
        $term_id = (int) $_POST["term_id"];
        $professor_id = (int) $_POST["professor_id"];
        list($building_name, $room_number) = explode(',', $_POST["location"]);
        $room_number = (int) $room_number;
        $meeting_day_id = (int) $_POST["meeting_days_id"];
        list($time_start, $time_end) = explode(',', $_POST["meeting_times"]);
        $class_max_capacity = (int) $_POST['class_max_capacity'];
        $class_id = (int) $_POST['class_id'];

        //query
        $edit_query = file_get_contents($queries_dir . 'classes_update.sql');
        $edit_stmt = $conn->prepare($edit_query);
        $edit_stmt->bind_param('isiisiissii', $course_id, $section, $term_id, $professor_id, $building_name, $room_number, $meeting_day_id, $time_start, $time_end, $class_max_capacity, $class_id);
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
    <title>Class Catalog</title>
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
        <h2>Classes</h2>
        <?php 
        result_to_html_table_with_checkbox_edit($classes_list, 'Delete?', 'selected[]', 'class_id', 'Delete Courses', 'delete_records');

        if(array_key_exists('edit_records', $_POST)){
            $query_classes_full = "SELECT * FROM classes_view_full";
            $select_stmt_classes_full = $conn->prepare($query_classes_full);
            $select_stmt_classes_full->execute();
            $result_classes_full = $select_stmt_classes_full->get_result();
            $classes_full_list = $result_classes_full->fetch_all(MYSQLI_BOTH);

            $row_index = $_POST['edit_records'];
            $original_record = $classes_full_list[$row_index];

            ?>
            <h3>Edit Classes</h3>
            <form method="post">
                <label for="class_id">Course ID</label>
                <input type="number" name="class_id" id="class_id" value="<?php echo $original_record['class_id']; ?>" readonly>
                <br>

                <!-- Course Select -->
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" required>
                    <?php foreach ($courses_list as $course) : ?>
                        <option value="<?= $course['course_id'] ?>" 
                            <?= ($course['course_id'] == $original_record['course_id']) ? 'selected' : ''; ?> >
                            <?= $course['course_discipline'] . ' ' . $course['course_number'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Section Input -->
                <label for="section">Section</label>
                <input type="text" name="section" id="section" value="<?= $original_record['section'] ?? 'a'; ?>" >
                <br>

                <!-- Term Select -->
                <label for="term_id">Term</label>
                <select name="term_id" id="term_id" required>
                    <?php foreach ($terms_list as $term) : ?>
                        <option value="<?= $term['term_id'] ?>" 
                            <?= ($term['term_id'] == $original_record['term_id']) ? 'selected' : ''; ?> >
                            <?= $term['term_start_date'] . ' ' . $term['term_end_date'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Professor Select -->
                <label for="professor_id">Professor</label>
                <select name="professor_id" id="professor_id" required>
                    <?php foreach ($professors_list as $professor) : ?>
                        <option value="<?= $professor['professor_id'] ?>" 
                            <?= ($professor['professor_id'] == $original_record['professor_id']) ? 'selected' : ''; ?>>
                            <?= $professor['professor_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Location Select -->
                <label for="location">Location</label>
                <select name="location" id="location" required>
                    <?php foreach ($locations_list as $location) : ?>
                        <option value="<?= $location['building_name'] . ',' . $location['room_number'] ?>" 
                            <?= ($location['building_name'] == $original_record['building_name'] && $location['room_number'] == $original_record['room_number']) ? 'selected' : ''; ?>>
                            <?= $location['building_name'] . ' ' . $location['room_number'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Meeting Days Select -->
                <label for="meeting_days_id">Schedule</label>
                <select name="meeting_days_id" id="meeting_days_id" required>
                    <?php foreach ($meeting_days_list as $meeting_day) : ?>
                        <option value="<?= $meeting_day['meeting_days_id'] ?>" 
                            <?= ($meeting_day['meeting_days_id'] == $original_record['meeting_days_id']) ? 'selected' : ''; ?>>
                            <?= $meeting_day['schedule'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Meeting Times Select -->
                <label for="meeting_times">Meeting Times</label>
                <select name="meeting_times" id="meeting_times" required>
                    <?php foreach ($meeting_times_list as $meeting_time) : ?>
                        <option value="<?= $meeting_time['time_start'] . ',' . $meeting_time['time_end'] ?>" 
                            <?= ($meeting_time['time_start'] == $original_record['time_start'] && $meeting_time['time_end'] == $original_record['time_end']) ? 'selected' : ''; ?>>
                            <?= $meeting_time['time_start'] . ' ' . $meeting_time['time_end'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>

                <!-- Max Capacity -->
                <label for="class_max_capacity">Max Capacity</label>
                <input type="number" name="class_max_capacity" id="class_max_capacity" value="<?= $original_record['class_max_capacity'] ?? 25 ?>">
                <br>

                <!-- Submit Button -->
                <button type="submit" name="complete_edit_records">Submit</button>
                </form>

        <?php } ?>

        <h3>Add Class</h3>
        <form method="post">

            <label for="course_id">Course</label>
            <select name="course_id" id="course_id" required>
                <option value="" selected disabled>Select a course</option>
                <?php foreach ($courses_list as $course) : ?>
                    <option value="<?= $course['course_id'] ?>"><?= $course['course_discipline'] . ' ' . $course['course_number'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <!-- Section Select -->
            <label for="section">Section</label>
            <input type="text" name="section" id="section" value="a">
            <br>

            <!-- Term Select -->
            <label for="term_id">Term</label>
            <select name="term_id" id="term_id" required>
                <option value="" selected disabled>Select a term</option>
                <?php foreach ($terms_list as $term) : ?>
                    <option value="<?= $term['term_id'] ?>"><?= $term['term_start_date'] . ' ' . $term['term_end_date'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <!-- Professor Select -->
            <label for="professor_id">Professor</label>
            <select name="professor_id" id="professor_id" required>
                <option value="" selected disabled>Select a professor</option>
                <?php foreach ($professors_list as $professor) : ?>
                    <option value="<?= $professor['professor_id'] ?>"><?= $professor['professor_name'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <!-- Location Select -->
            <label for="location">Location</label>
            <select name="location" id="location" required>
                <option value="" selected disabled>Select a location</option>
                <?php foreach ($locations_list as $location) : ?>
                    <option value="<?= $location['building_name'] . ',' . $location['room_number'] ?>"><?= $location['building_name'] . ' ' . $location['room_number'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <!-- Meeting Days Select -->
            <label for="meeting_days_id">Schedule</label>
            <select name="meeting_days_id" id="meeting_days_id" required>
                <option value="" selected disabled>Select schedule</option>
                <?php foreach ($meeting_days_list as $meeting_day) : ?>
                    <option value="<?php echo $meeting_day['meeting_days_id']; ?>"><?php echo $meeting_day['schedule']; ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <!-- Meeting Times Select -->
            <label for="meeting_times">Meeting Times</label>
            <select name="meeting_times" id="meeting_times" required>
                <option value="" selected disabled>Select meeting time</option>
                <?php foreach ($meeting_times_list as $meeting_time) : ?>
                    <option value="<?= $meeting_time['time_start'] . ',' . $meeting_time['time_end'] ?>"><?= $meeting_time['time_start'] . ' ' . $meeting_time['time_end'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            
            <label for="class_max_capacity">Max Capacity</label>
            <input type="number" name="class_max_capacity" id="class_max_capacity" $value="25">
            <br>

            <!-- Submit Button -->
            <button type="submit" name="add_records">Submit</button>
        </form>
    </main>
    <footer><p>&copy; 2024 Kendianawa University. All rights reserved.</p></footer>
    <?php $conn->close(); ?>

</body>
</html>
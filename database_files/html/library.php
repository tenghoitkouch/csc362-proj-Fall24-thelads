<?php
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
        <table>
            <thead>
                <tr>
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
                    <?php for($j=0; $j<$n_cols; $j++){ ?>
                        <td><?php echo $qryres[$i][$j]; ?></td>
                    <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
<?php } ?>

<?php
    function result_to_html_table_with_del_checkbox($result) {
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
                                <td><input type="checkbox" name="checkbox<?php echo $id; ?>" value="<?php echo $id; ?>"/></td>
                                <?php for($j=0; $j<$n_cols; $j++){ ?>
                                    <td><?php echo $qryres[$i][$j]; ?></td>
                                <?php } ?>
                            </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p><input type="submit" name="delbtn" value="Delete Records" /></p>
        </form>
<?php } ?>

<?php

function result_to_html_table_with_checkbox($result, $title, $array_name, $field_value, $submit_value, $submit_name){
    ?>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <td><?php echo $title; ?></td>
                    <?php
                        foreach(array_keys($result[0]) as $key){
                            if (!is_numeric($key)) {
                                echo '<td><b>' . $key . '</b></td>';
                            }
                        }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($result as $row => $row_data){
                        ?>
                            <tr>
                                <?php
                                    echo '<td><input type="checkbox" name="' . $array_name . '" value="' . $row_data[$field_value] . '"></td>';
                                    foreach($row_data as $key => $value){
                                        if (!is_numeric($key)) {
                                            echo '<td>' . $value . '</td>';
                                        }
                                    }
                                ?>
                            </tr>
                        <?php
                        
                    }
                ?>
            </tbody>
        </table>
        <input type="submit" value="<?php echo $submit_value; ?>" name="<?php echo $submit_name; ?>">
    </form>
    <?php
}

function result_to_html_table_with_checkbox_edit($result, $title, $array_name, $field_value, $submit_value, $submit_name){
    ?>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <td><?php echo $title; ?></td>
                    <?php
                        foreach(array_keys($result[0]) as $key){
                            if (!is_numeric($key)) {
                                echo '<td><b>' . $key . '</b></td>';
                            }
                        }
                    ?>
                    <td>Edit</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($result as $row => $row_data){
                        ?>
                            <tr>
                                <?php
                                    echo '<td><input type="checkbox" name="' . $array_name . '" value="' . $row_data[$field_value] . '"></td>';
                                    foreach($row_data as $key => $value){
                                        if (!is_numeric($key)) {
                                            echo '<td>' . $value . '</td>';
                                        }
                                    }
                                    echo '<td><button type="submit" name="edit_records" value="' . $row . '">Edit</button></td>';
                                ?>
                            </tr>
                        <?php
                        
                    }
                ?>
            </tbody>
        </table>
        <button type="submit" name="<?php echo $submit_name; ?>"><?php echo $submit_value; ?></button>

    </form>
    <?php
}

?>




<?php

    $table_queries = [
        "buildings" => "SELECT * FROM buildings_view;", 
        "classes" => "SELECT * FROM classes_view;",
        "course_prerequisites" => null,
        "courses" => "SELECT * FROM courses_view;",
        "days" => "SELECT * FROM days_view;",
        "degree_requirements" => null,
        "degrees" => "SELECT * FROM degrees_view;",
        "locations" => "SELECT * FROM locations_view;",
        "meeting_days" => "SELECT * FROM meeting_days_view;",
        "meeting_times" => "SELECT * FROM meeting_times_view;",
        "professors" => "SELECT * FROM professors_view",
        "student_class_history" => null,
        "students" => "SELECT * FROM students_view",
        "terms" => "SELECT * FROM terms_view;"
    ];

    function generate_select_fields($conn, $tables, $original_record=NULL){
        global $table_queries;

        ?>
            <form method="post">
                <?php
                    foreach($tables as $table){

                        //if it is not table, then we just put a simple text input
                        if(!array_key_exists($table, $table_queries)){
                            ?>
                            <label for="<?php echo $table; ?>"> <?php echo $table; ?> </label>
                            <input type="text" name="<?php echo $table; ?>" id="<?php echo $table; ?>" value="<?php echo $original_record ? $original_record[$table] : ''; ?>" required>
                            <?php
                            continue;
                        }

                        //querying
                        $query = $table_queries[$table];
                        $select_stmt = $conn->prepare($query);
                        $select_stmt->execute();
                        $result = $select_stmt->get_result();
                        $result_dict = $result->fetch_all(MYSQLI_ASSOC); //gives us a dict/map
                        
                        //this name below will be appended to $_POST
                        ?> 
                            <label for="<?php echo $table; ?>"><?php echo $table; ?></label>
                            <select name="<?php echo $table; ?>" id="<?php echo $table; ?>" required> 
                            <?php 
                                foreach($result_dict as $record){
                                    echo generate_options($table, $record, $original_record);
                                }
                            ?>
                        </select>
                    <?php } ?>
                    <?php 
                        if(is_null($original_record)){
                            echo '<input type="submit" value="Add Records" name="add_records">';
                        }else{
                            foreach($original_record as $key => $value){
                                echo '<input type="hidden" name="original_' . $key . '" value="' . $value . '">';
                            }
                            echo '<input type="submit" value="Edit Records" name="edit_records">';
                        }
                    ?>
                    
            </form>
<?php } ?>

<?php 
    function generate_options($table_name, $record, $original_record=null){
        
        // $option_statements = [
        //     "buildings" => '<option value="' . $record['building_name'] . '">' . $record['building_name'] . '</option>',
        //     "classes" => '<option value="' . $record['class_id'] . '">' 
        //                     . $record['class_code'] . ': ' . $record['course_name'] . ', ' . $record['professor_name'] . ', ' . $record['term'] .'</option>',
        //     "course_prerequisites" => null,
        //     "courses" => '<option value="' . $record['course_id'] . '">' . $record['course_discipline'] . ' ' . $record['course_number'] . '</option>',
        //     "days" => '<option value="' . $record['day_letter'] . '">' . $record['day_letter'] . '</option>',
        //     "degree_requirements" => null,
        //     "degrees" => '<option value="' . $record['degree_id'] . '">' . $record['degree_name'] . '</option>',
        //     "locations" => '<option value="' . $record['building_name'] . ',' . $record['room_number'] . '">' . $record['building_name'] . ' ' . $record['room_number'] . '</option>',
        //     "meeting_days" => '<option value="' . $record['meeting_days_id'] . '">' . $record['schedule'] . '</option>',
        //     "meeting_times" => '<option value="' . $record['time_start'] . ',' . $record['time_end'] . '">' . $record['time_start'] . ' - ' . $record['time_end'] . '</option>',
        //     "professors" => '<option value="' . $record['professor_id'] . '">' . $record['full_name'] . '</option>',
        //     "student_class_history" => null,
        //     "students" => '<option value="' . $record['student_id'] . '">' . $record['full_name'] . '</option>',
        //     "terms" => '<option value="' . $record['term_id'] . '">' . $record['term_start_date'] . ' - ' . $record['term_end_date'] . '</option>',
        // ];

        $option_fields = [
            "buildings" => [
                "value" => ['building_name'],
                "label" => ['building_name']
            ], 
            "classes" => [
                "value" => ['class_id'],
                "label" => ['class_code', 'course_name', 'professor_name', 'term']
            ],
            "course_prerequisites" => [
                "value" => [],
                "label" => []
            ],
            "courses" => [
                "value" => ['course_id'],
                "label" => ['course_discipline', 'course_number']
            ],
            "days" => [
                "value" => ['day_letter'],
                "label" => ['day_letter']
            ],
            "degree_requirements" => [
                "value" => [],
                "label" => []
            ],
            "degrees" => [
                "value" => ['degree_id'],
                "label" => ['degree_name']
            ],
            "locations" => [
                "value" => ['building_name', 'room_number'],
                "label" => ['building_name', 'room_number']
            ],
            "meeting_days" => [
                "value" => ['meeting_days_id'],
                "label" => ['schedule']
            ],
            "meeting_times" => [
                "value" => ['time_start', 'time_end'],
                "label" => ['time_start', 'time_end']
            ],
            "professors" => [
                "value" => ['professor_id'],
                "label" => ['full_name']
            ],
            "student_class_history" => [
                "value" => [],
                "label" => []
            ],
            "students" => [
                "value" => ['student_id'],
                "label" => ['full_name']
            ],
            "terms" => [
                "value" => ['term_id'],
                "label" => ['term_start_date', 'term_end_date']
            ]
        ];
        
        $value = array_map(function($key) use ($record){
            return $record[$key];
        }, $option_fields[$table_name]['value']);

        $label = array_map(function($key) use ($record){
            return $record[$key];
        }, $option_fields[$table_name]['label']);

        if($original_record === NULL){ return '<option value="' . implode(',', $value) . '">' . implode(' - ', $label) . '</option>'; }
        
        $original_value = array_map(function($key) use ($original_record){
            return $original_record[$key];
        }, $option_fields[$table_name]['value']);

        if($original_value == $value){
            return '<option value="' . implode(',', $value) . '" selected >' . implode(' - ', $label) . '</option>';
        }else{
            return '<option value="' . implode(',', $value) . '">' . implode(' - ', $label) . '</option>';
        }


        //IMPORTANT: locations, meeting_times have 2 values, which will need spliting
        // return $option_statements[$table_name];
    }
?>

<?php

    function generate_edit_selections($result){
        
        ?><form method="get">
            <label for="selected_record">Select: </label>
            <select name="selected_record" id="selected_record" required>
                <?php
                    for($row = 0; $row < $result->num_rows; $row++){
                        $row_data = $result->fetch_assoc();
                        $option_str = implode(" | ", array_values($row_data));
                        echo '<option value="' . $row . '">' . $option_str . '</option>';
                    }
                ?>
            </select>
            <input type="submit" name="edit_records" value="Edit Records">
        </form>

    <?php }

?>

<?php
    function build_nav(){
        echo '<a href="home.php">Home</a>';
        if (isset($_SESSION['designation']) && $_SESSION['designation'] == 'student'){
            ?>
            <a href="transcript.php">Transcript</a>
            <a href="">Schedule</a>
            <a href="degree_requirements.php">Degree Requirements</a>
            <a href="class_catalog.php">Class Catalog</a>
            <a href="class_registration.php">Class Registration</a>
        <?php
        }elseif (isset($_SESSION['designation']) && $_SESSION['designation'] == 'admin'){
            ?>
            <a href="student_transcript.php">Transcript</a>
            <a href="degree_req_edit.php">Degree Requirements</a>
            <a href="courses_admin.php">Courses</a>
            <a href="classes_admin.php">Classes</a>
            <a href="">Buildings</a>
            <a href="">Locations</a>
            <a href="professors_admin.php">Professors</a>
            <a href="students_admin.php">Students</a>
            <a href="">Terms</a>
            <a href="">Meeting Days</a>
            <a href="">Meeting Times</a>
        <?php
        }
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == TRUE){
            echo '<form method="post"><button type="submit" name="logout">Logout</button></form>';
        }
    }
?>


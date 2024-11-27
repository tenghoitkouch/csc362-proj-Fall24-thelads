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
            <p><input type="submit" name="delbtn" value="Delete Selected Records" /></p>
        </form>
<?php } ?>


<?php
    function add_new_records($conn, $tables){
        
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

        ?>
            <form method="post">
                <?php
                    foreach($tables as $table){

                        //if it is not table, then we just put a simple text input
                        if(!array_key_exists($table, $table_queries)){
                            ?>
                            <label for="<?php echo $table; ?>"> <?php echo $table; ?> </label>
                            <input type="text" name="<?php echo $table; ?>" id="<?php echo $table; ?>">
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
                        ?> <select name="<?php echo $table; ?>" required> 
                            <?php 
                                foreach($result_dict as $record){
                                    echo generate_options($table, $record);
                                }
                            ?>
                        </select>
                    <?php } ?>
                    <input type="submit" value="add_records" name="add_records">
            </form>
<?php } ?>

<?php 
    function generate_options($table_name, $record){
        
        $option_statements = [
            "buildings" => '<option value="' . $record['building_name'] . '">' . $record['building_name'] . '</option>',
            "classes" => '<option value="' . $record['class_id'] . '">' 
                            . $record['class_code'] . ': ' . $record['course_name'] . ', ' . $record['professor_name'] . ', ' . $record['term'] .'</option>',
            "course_prerequisites" => null,
            "courses" => '<option value="' . $record['course_id'] . '">' . $record['course_discipline'] . ' ' . $record['course_number'] . '</option>',
            "days" => '<option value="' . $record['day_letter'] . '">' . $record['day_letter'] . '</option>',
            "degree_requirements" => null,
            "degrees" => '<option value="' . $record['degree_id'] . '">' . $record['degree_name'] . '</option>',
            "locations" => '<option value="' . $record['building_name'] . ',' . $record['room_number'] . '">' . $record['building_name'] . ' ' . $record['room_number'] . '</option>',
            "meeting_days" => '<option value="' . $record['meeting_days_id'] . '">' . $record['schedule'] . '</option>',
            "meeting_times" => '<option value="' . $record['time_start'] . ',' . $record['time_end'] . '">' . $record['time_start'] . ' - ' . $record['time_end'] . '</option>',
            "professors" => '<option value="' . $record['professor_id'] . '">' . $result['full_name'] . '</option>',
            "student_class_history" => null,
            "students" => '<option value="' . $record['student_id'] . '">' . $result['full_name'] . '</option>',
            "terms" => '<option value="' . $record['term_id'] . '">' . $record['term_start_date'] . ' - ' . $record['term_end_date'] . '</option>',
        ];

        //IMPORTANT: locations, meeting_times have 2 values, which will need spliting
        return $option_statements[$table_name];
    }
?>





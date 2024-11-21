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




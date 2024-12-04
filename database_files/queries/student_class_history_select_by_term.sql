SELECT * 
FROM student_class_history_view
    WHERE   student_id = ?
    AND     term = ?;

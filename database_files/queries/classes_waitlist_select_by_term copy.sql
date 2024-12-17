SELECT * 
FROM classes_waitlist_view
    WHERE   student_id = ?
    AND     term = ?;

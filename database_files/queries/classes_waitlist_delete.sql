DELETE FROM classes_waitlist
WHERE   student_id = ?
        AND class_id = ?;
UPDATE students
SET 
    student_first_name = ?,
    student_last_name = ?,
    student_email = ?,
    student_phone_number = ?,
    student_street = ?,
    student_city = ?,
    student_state = ?,
    student_zip_code = ?,
    professor_id = ?
WHERE 
    student_id = ?;

UPDATE professors
SET 
    professor_first_name = ?,
    professor_last_name = ?,
    professor_email = ?,
    professor_phone_number = ?,
    professor_street = ?,
    professor_city = ?,
    professor_state = ?,
    professor_zip_code = ?
WHERE 
    professor_id = ?;

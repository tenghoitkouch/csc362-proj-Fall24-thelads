INSERT INTO classes (course_id, 
                    section, 
                    term_id, 
                    professor_id,
                    building_name, 
                    room_number, 
                    meeting_days_id, 
                    time_start, 
                    time_end
                    )
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);

-- make section 'a'
-- if want to edit class_capacity, do on alters
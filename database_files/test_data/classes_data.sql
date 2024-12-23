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
VALUES  (1, "a", 1, 1, "OLIN", 201, 6, "08:00:00", "09:00:00"), -- 1
        (2, "a", 1, 1, "OLIN", 201, 6, "09:10:00", "10:10:00"), -- 2

        (3, "a", 1, 2, "YOUNG", 101, 6, "10:20:00", "11:20:00"), -- 3
        (4, "b", 1, 3, "CROUNSE", 102, 6, "11:30:00", "12:30:00"), -- 4
        (5, "a", 1, 4, "YOUNG", 102, 6, "12:40:00", "13:40:00"), -- 5
        (6, "a", 1, 5, "OLIN", 202, 6, "13:50:00", "14:50:00"), -- 6
        (7, "b", 1, 6, "YOUNG", 103, 6, "15:00:00", "16:00:00"), -- 7
        (8, "a", 1, 7, "CROUNSE", 201, 6, "08:00:00", "09:00:00"), -- 8
        (9, "b", 1, 8, "YOUNG", 203, 6, "09:10:00", "10:10:00"), -- 9
        (10, "a", 1, 9, "CROUNSE", 103, 6, "10:20:00", "11:20:00"), -- 10
        (1, "b", 1, 10, "OLIN", 203, 6, "11:30:00", "12:30:00"), -- 11
        (2, "c", 1, 1, "YOUNG", 201, 6, "12:40:00", "13:40:00"); -- 12

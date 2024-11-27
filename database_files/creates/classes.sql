CREATE TABLE classes (
    class_id            INT AUTO_INCREMENT,
    course_id           INT,
    term_id             INT,
    building_name       VARCHAR(64),
    room_number         INT,
    professor_id        INT,
    section             VARCHAR(1) DEFAULT 'a',
    class_max_capacity  INT DEFAULT 25,
    meeting_days_id     INT,
    time_start          TIME,
    time_end            TIME,
    PRIMARY KEY (class_id),
    FOREIGN KEY (course_id) REFERENCES courses (course_id) ON DELETE RESTRICT,
    FOREIGN KEY (term_id) REFERENCES terms (term_id) ON DELETE RESTRICT,
    FOREIGN KEY (building_name, room_number) REFERENCES locations (building_name, room_number) ON DELETE SET NULL,
    FOREIGN KEY (professor_id) REFERENCES professors (professor_id) ON DELETE RESTRICT,
    FOREIGN KEY (meeting_days_id) REFERENCES meeting_days (meeting_days_id) ON DELETE RESTRICT,
    FOREIGN KEY (time_start, time_end) REFERENCES meeting_times (time_start, time_end) ON DELETE RESTRICT,

    CONSTRAINT unique_class UNIQUE (course_id, term_id, section) -- b/c these needs to be unique
);


CREATE VIEW classes_view AS
SELECT  class_id, 
        CONCAT(crs.course_discipline, ' ', crs.course_number, cls.section) AS class_code, 
        crs.course_name AS course_name,
        CONCAT(professor_first_name, ' ', professor_last_name) AS professor_name, 
        CONCAT(building_name, ' ', room_number) AS 'location', 
        GROUP_CONCAT(day_letter) AS meeting_days,
        CONCAT(time_start, ' ', time_end) AS meeting_times,
        CONCAT(term_start_date, ' - ', term_end_date) AS term, 
        class_max_capacity,
        cpr.prerequisite AS prerequisites
        FROM classes AS cls
        JOIN terms
            USING (term_id)
        JOIN professors
            USING (professor_id)
        JOIN meeting_days
            USING (meeting_days_id)
        JOIN courses as crs
            ON cls.course_id = crs.course_id
        LEFT OUTER JOIN course_prerequisites_view AS cpr
            ON cls.course_id = cpr.course_id
GROUP BY    class_id
ORDER BY    term_id DESC,
            class_code ASC;
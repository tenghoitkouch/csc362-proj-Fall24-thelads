CREATE TABLE classes_waitlist(
    student_id          INT,
    class_id            INT,
    waitlist_timestamp  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES students (student_id) ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE RESTRICT
);

CREATE VIEW classes_waitlist_view AS
SELECT      student_id,
            CONCAT(student_first_name, ' ', student_last_name) AS student_name,
            class_id,
            course_discipline, 
            course_number,
            section,
            course_name,
            term
FROM        classes_waitlist
            JOIN students
            USING (student_id)
            JOIN classes_view
            USING (class_id)
ORDER BY    term DESC,
            course_discipline ASC,
            course_number ASC,
            section ASC;



            
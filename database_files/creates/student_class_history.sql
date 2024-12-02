CREATE TABLE student_class_history(
    student_id  INT,
    class_id    INT,
    grade       FLOAT(4) DEFAULT NULL,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES students (student_id) ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE RESTRICT
);

CREATE VIEW student_class_history_view AS
SELECT  CONCAT(student_first_name, ' ', student_last_name) AS student_name,
        course_code, 
        section,
        course_name,
        term,
        grade
FROM    student_class_history
        JOIN students
        USING (student_id)
        JOIN classes_view
        USING (class_id)
ORDER BY    term DESC,
            course_code ASC,
            section ASC;

        
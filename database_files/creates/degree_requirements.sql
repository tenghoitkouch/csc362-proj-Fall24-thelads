CREATE TABLE degree_requirements(
    degree_id  INT,
    course_id    INT,
    PRIMARY KEY (degree_id, course_id),
    FOREIGN KEY (degree_id) REFERENCES degrees (degree_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses (course_id) ON DELETE RESTRICT
);

CREATE VIEW degree_requirements_view AS
SELECT  degree_name,
        CONCAT(course_discipline, ' ', course_number) AS course_code
        course_name,
FROM    degree_requirements
        JOIN degrees
        USING (degree_id)
        JOIN courses
        USING (course_id)
ORDER BY    degree_name ASC,
            course_code ASC;
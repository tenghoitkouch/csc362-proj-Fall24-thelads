CREATE TABLE courses (
  course_id           INT AUTO_INCREMENT,
  course_discipline   VARCHAR(50),
  course_number       INT,
  course_name         VARCHAR(128),
  course_credits      INT,
  course_description  VARCHAR(200),
  PRIMARY KEY (course_id)
);
CREATE VIEW courses_view AS
SELECT  course_id,
        course_discipline,
        course_number,
        course_name,
        course_credits,
        course_description
        FROM courses
GROUP BY course_id
ORDER BY  course_discipline ASC,
          course_number ASC;

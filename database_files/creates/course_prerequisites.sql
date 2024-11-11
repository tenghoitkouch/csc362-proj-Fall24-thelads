CREATE TABLE course_prerequisites(
  course_id       INT,
  prerequisite_id INT,
  PRIMARY KEY (course_id, prerequisite_id),
  FOREIGN KEY (course_id) REFERENCES courses (course_id) ON DELETE RESTRICT,
  FOREIGN KEY (prerequisite_id) REFERENCES courses (course_id) ON DELETE RESTRICT
);

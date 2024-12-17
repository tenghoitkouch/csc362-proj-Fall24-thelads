DROP DATABASE IF EXISTS ku_registrar;
CREATE DATABASE ku_registrar;

USE ku_registrar;

-- creates statments
SOURCE creates/days.sql;
SOURCE creates/terms.sql;
SOURCE creates/courses.sql;
SOURCE creates/degrees.sql;
SOURCE creates/degree_requirements.sql;
SOURCE creates/professors.sql;
SOURCE creates/students.sql;
SOURCE creates/buildings.sql;
SOURCE creates/locations.sql;
SOURCE creates/meeting_times.sql;
SOURCE creates/meeting_days.sql;
SOURCE creates/course_prerequisites.sql;
SOURCE creates/classes.sql;
SOURCE creates/student_class_history.sql;
SOURCE creates/classes_waitlist.sql;
SOURCE creates/users.sql;

-- test data, order matters here
SOURCE test_data/courses_data.sql;
SOURCE test_data/terms_data.sql;
SOURCE test_data/professors_data.sql;
SOURCE test_data/buildings_data.sql;
SOURCE test_data/locations_data.sql;
SOURCE test_data/meeting_days_data.sql;
SOURCE test_data/meeting_times_data.sql;
SOURCE test_data/course_prerequisites_data.sql;
SOURCE test_data/degrees_data.sql;
SOURCE test_data/degree_requirements_data.sql;
SOURCE test_data/students_data.sql;
SOURCE test_data/classes_data.sql;
SOURCE test_data/student_class_history_data.sql;
SOURCE test_data/classes_waitlist_data.sql;
SOURCE test_data/users_data.sql;

-- checking if everythings good
SELECT * FROM buildings_view;
SELECT * FROM locations_view;
SELECT * FROM meeting_days_view;
SELECT * FROM meeting_times_view;
SELECT * FROM professors_view;
SELECT * FROM terms_view;
SELECT * FROM courses_view;
SELECT * FROM course_prerequisites_view;
SELECT * FROM degrees_view;
SELECT * FROM degree_requirements_view;
SELECT * FROM students_view;
SELECT * FROM classes_view;
SELECT * FROM student_class_history_view;
SELECT * FROM classes_waitlist_view;
SELECT * FROM users_view;


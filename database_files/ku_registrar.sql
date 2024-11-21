DROP DATABASE IF EXISTS ku_registrar;
CREATE DATABASE ku_registrar;

USE ku_registrar;

--creates statments
SOURCE creates/days.sql;
SOURCE creates/terms.sql;
SOURCE creates/classes.sql;
SOURCE creates/degree_requirements.sql;
SOURCE creates/student_class_history.sql;
SOURCE creates/buildings.sql;
SOURCE creates/locations.sql;
SOURCE creates/courses.sql;
SOURCE creates/degrees.sql;
SOURCE creates/meeting_times.sql;
SOURCE creates/meeting_days.sql;
SOURCE creates/course_prerequisites.sql;

--test data
SOURCE test_data/courses_data.sql;
SOURCE test_data/terms_data.sql;
SOURCE test_data/professors_data.sql;
SOURCE test_data/buildings_data.sql;
SOURCE test_data/locations_data.sql;
SOURCE test_data/meeting_days_data.sql;
SOURCE test_data/meeting_times_data.sql;
SOURCE test_data/courses_data.sql;
SOURCE test_data/course_prerequisites_data.sql;
SOURCE test_data/classes_data.sql;


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
        crs.course_discipline AS course_discipline,
        crs.course_number AS course_number,
        cls.section AS section,
        crs.course_name AS course_name,
        CONCAT(professor_first_name, ' ', professor_last_name) AS professor_name, 
        building_name,
        room_number,
        GROUP_CONCAT(day_letter) AS schedule,
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
ORDER BY    term DESC,
            course_discipline ASC,
            course_number ASC,
            section ASC;

CREATE VIEW classes_view_full AS
SELECT  class_id, 
        crs.course_id AS course_id,
        crs.course_discipline AS course_discipline,
        crs.course_number AS course_number,
        cls.section AS section,
        crs.course_name AS course_name,
        professor_id,
        CONCAT(professor_first_name, ' ', professor_last_name) AS professor_name, 
        building_name,
        room_number,
        meeting_days_id,
        GROUP_CONCAT(day_letter) AS schedule,
        time_start,
        time_end,
        CONCAT(time_start, ' ', time_end) AS meeting_times,
        term_id,
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
ORDER BY    term DESC,
            course_discipline ASC,
            course_number ASC,
            section ASC;

DROP FUNCTION IF EXISTS get_term_id_by_class;
CREATE FUNCTION get_term_id_by_class(class_id_input INT)
RETURNS INT
RETURN (
    SELECT term_id
    FROM    classes
    WHERE   class_id = class_id_input
);

DROP FUNCTION IF EXISTS get_time_start_by_class;
CREATE FUNCTION get_time_start_by_class(class_id_input INT)
RETURNS INT
RETURN (
    SELECT time_start
    FROM    classes
    WHERE   class_id = class_id_input
);

DROP FUNCTION IF EXISTS get_time_end_by_class;
CREATE FUNCTION get_time_end_by_class(class_id_input INT)
RETURNS INT
RETURN (
    SELECT time_end
    FROM    classes
    WHERE   class_id = class_id_input
);

DROP FUNCTION IF EXISTS get_class_max_capacity;
CREATE FUNCTION get_class_max_capacity(class_id_input INT)
RETURNS INT
RETURN (
    SELECT class_max_capacity
    FROM    classes
    WHERE   class_id = class_id_input
);


DROP FUNCTION IF EXISTS get_num_class_by_location_term_time;
CREATE FUNCTION get_num_class_by_location_term_time(
    building_name_input VARCHAR(64), 
    room_number_input INT, 
    term_id_input INT,
    time_start_input TIME, 
    time_end_input TIME)
RETURNS INT
RETURN (
    SELECT COUNT(class_id)
        FROM    classes
        WHERE   building_name = building_name_input
                AND room_number = room_number_input
                AND term_id = term_id_input
                AND (
                    ((time_start >= time_start_input) AND (time_start <= time_end_input))
                    OR
                    ((time_end >= time_start_input) AND (time_end <= time_end_input))
                )             
);

DROP FUNCTION IF EXISTS get_num_class_by_professor_term_time;
CREATE FUNCTION get_num_class_by_professor_term_time(
    professor_id_input INT, 
    term_id_input INT,
    time_start_input TIME, 
    time_end_input TIME)
RETURNS INT
RETURN (
    SELECT COUNT(class_id)
        FROM    classes
        WHERE   professor_id = professor_id_input
                AND term_id = term_id_input
                AND (
                    ((time_start >= time_start_input) AND (time_start <= time_end_input))
                    OR
                    ((time_end >= time_start_input) AND (time_end <= time_end_input))
                )             
);


DELIMITER $$
CREATE TRIGGER classes_insert
BEFORE INSERT ON classes FOR EACH ROW
BEGIN

    -- room conflict
    SET @location_existing_classes = get_num_class_by_location_term_time(NEW.building_name, NEW.room_number, NEW.term_id, NEW.time_start, NEW.time_end);
    IF (@location_existing_classes <> 0) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Class already exists at that location on that time';
    END IF;

    -- professor conflict
    SET @professor_existing_classes = get_num_class_by_professor_term_time(NEW.professor_id, NEW.term_id, NEW.time_start, NEW.time_end);
    IF (@professor_existing_classes <> 0) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professor is already teaching a class on that time';
    END IF;

END; $$
DELIMITER ;


DELIMITER $$
CREATE TRIGGER classes_update
BEFORE UPDATE ON classes FOR EACH ROW
BEGIN

    -- room conflict
    IF (NEW.building_name <> OLD.building_name) OR (NEW.room_number <> OLD.room_number) THEN
        SET @location_existing_classes = get_num_class_by_location_term_time(NEW.building_name, NEW.room_number, NEW.term_id, NEW.time_start, NEW.time_end);
        IF (@location_existing_classes <> 0) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Class already exists at that location on that time';
        END IF;
    END IF;

    -- professor conflict
    IF (NEW.professor_id <> OLD.professor_id) THEN
        SET @professor_existing_classes = get_num_class_by_professor_term_time(NEW.professor_id, NEW.term_id, NEW.time_start, NEW.time_end);
        IF (@professor_existing_classes <> 0) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professor is already teaching a class on that time';
        END IF;
    END IF;

END; $$
DELIMITER ;

CREATE TABLE student_class_history(
    student_id  INT,
    class_id    INT,
    grade       FLOAT(4) DEFAULT NULL,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES students (student_id) ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE RESTRICT
);

CREATE VIEW student_class_history_view AS
SELECT  sch.student_id as student_id,
        CONCAT(student_first_name, ' ', student_last_name) AS student_name,
        class_id,
        course_discipline, 
        course_number,
        section,
        course_name,
        term,
        grade
FROM    student_class_history as sch
        JOIN students as s
        ON sch.student_id = s.student_id
        JOIN classes_view
        USING (class_id)
ORDER BY    term DESC,
            course_discipline ASC,
            course_number ASC,
            section ASC;


CREATE VIEW student_class_history_view_min AS
SELECT  *
FROM    student_class_history
        JOIN classes
        USING (class_id);


DROP FUNCTION IF EXISTS get_class_current_size;
CREATE FUNCTION get_class_current_size(class_id_input INT)
RETURNS INT
RETURN (
    SELECT COUNT(student_id)
    FROM    student_class_history
    WHERE   class_id = class_id_input
    GROUP BY class_id
);


DROP FUNCTION IF EXISTS get_num_student_class_by_term_time;
CREATE FUNCTION get_num_student_class_by_term_time(
    term_id_input INT,
    time_start_input TIME, 
    time_end_input TIME,
    student_id_input INT)
RETURNS INT
RETURN (
    SELECT COUNT(class_id)
        FROM    student_class_history_view_min
        WHERE   student_id = student_id_input
                AND term_id = term_id_input
                AND (
                    ((time_start >= time_start_input) AND (time_start <= time_end_input))
                    OR
                    ((time_end >= time_start_input) AND (time_end <= time_end_input))
                )             
);

DELIMITER $$
CREATE TRIGGER student_class_history_insert
BEFORE INSERT ON student_class_history FOR EACH ROW
BEGIN

    SET @current_class_size = get_class_current_size(NEW.class_id);
    SET @max_capacity = get_class_max_capacity(NEW.class_id);

    -- class size constraint
    IF (@current_class_size >= @max_capacity) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Class is currently full';
    END IF;

    -- time constraint
    SET @current_term = get_term_id_by_class(NEW.class_id);
    SET @current_time_start = get_time_start_by_class(NEW.class_id);
    SET @current_time_end = get_time_end_by_class(NEW.class_id);
    SET @num_classes_at_current_time = get_num_student_class_by_term_time(@current_term, @current_time_start, @current_time_end, NEW.student_id);

    IF (@num_classes_at_current_time >= 0) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Already enrolled in another class at that time';
    END IF;

END; $$
DELIMITER ;        


DELIMITER $$
CREATE TRIGGER student_class_history_update
BEFORE UPDATE ON student_class_history FOR EACH ROW
BEGIN

    SET @current_class_size = get_class_current_size(NEW.class_id);
    SET @max_capacity = get_class_max_capacity(NEW.class_id);

    -- class size constraint
    IF (@current_class_size >= @max_capacity) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Class is currently full';
    END IF;     

    -- time constraint
    SET @current_term = get_term_id_by_class(NEW.class_id);
    SET @current_time_start = get_time_start_by_class(NEW.class_id);
    SET @current_time_end = get_time_end_by_class(NEW.class_id);
    SET @num_classes_at_current_time = get_num_student_class_by_term_time(@current_term, @current_time_start, @current_time_end, NEW.student_id);

    IF (@num_classes_at_current_time >= 0) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Already enrolled in another class at that time';
    END IF; 

END; $$
DELIMITER ;        
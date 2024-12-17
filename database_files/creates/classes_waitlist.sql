CREATE TABLE classes_waitlist(
    student_id          INT,
    class_id            INT,
    waitlist_timestamp  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES students (student_id) ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE RESTRICT
);

CREATE VIEW classes_waitlist_view AS
SELECT *
FROM        classes_waitlist
ORDER BY    class_id DESC,
            waitlist_timestamp ASC;

            
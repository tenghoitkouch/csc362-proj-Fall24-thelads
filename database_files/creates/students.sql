-- Creating the students table
CREATE TABLE students (
    student_id INT PRIMARY KEY,
    student_first_name VARCHAR(50) NOT NULL,
    student_last_name VARCHAR(50) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    student_phone_number VARCHAR(15),
    student_street VARCHAR(100),
    student_city VARCHAR(50),
    student_state VARCHAR(50),
    student_zip_code VARCHAR(10),
    professor_id INT,
    FOREIGN KEY (professor_id) REFERENCES professors(professor_id) ON DELETE SET NULL
);

-- Optional view for listing student details
CREATE VIEW student_details AS
SELECT 
    student_id, 
    CONCAT(student_first_name, ' ', student_last_name) AS full_name,
    student_email, 
    student_phone_number, 
    student_city, 
    student_state
FROM students;

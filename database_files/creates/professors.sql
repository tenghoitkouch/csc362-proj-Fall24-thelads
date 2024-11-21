-- Creating the professors table
CREATE TABLE professors (
    professor_id INT AUTO_INCREMENT,
    professor_first_name VARCHAR(50) NOT NULL,
    professor_last_name VARCHAR(50) NOT NULL,
    professor_email VARCHAR(100) NOT NULL,
    professor_phone_number VARCHAR(15),
    professor_street VARCHAR(100),
    professor_city VARCHAR(50),
    professor_state VARCHAR(50),
    professor_zip_code VARCHAR(10),
    PRIMARY KEY (professor_id)
);

-- Optional view for listing professor details
CREATE VIEW professors_view AS
SELECT 
    professor_id, 
    CONCAT(professor_first_name, ' ', professor_last_name) AS full_name,
    professor_email, 
    professor_phone_number, 
    professor_city, 
    professor_state
FROM professors;

CREATE TABLE roles (
    designation    VARCHAR(64),
    PRIMARY KEY (designation)
);

INSERT INTO roles (designation)
VALUES  ("student"),
        ("admin");

CREATE TABLE users (
    user_id         INT AUTO_INCREMENT,
    user_name       VARCHAR(128) NOT NULL,
    user_password   VARCHAR(256) NOT NULL,
    designation     VARCHAR(64),
    designation_id  INT,
    FOREIGN KEY (designation) REFERENCES roles (designation) ON DELETE RESTRICT,
    PRIMARY KEY (user_id)
);

CREATE VIEW users_view AS
SELECT * FROM users;


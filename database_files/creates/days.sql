CREATE TABLE days (
  day_letter  CHAR(8),
  PRIMARY KEY (day_letter)
);

INSERT INTO days (day_letter)
VALUES ('M'),
       ('T'),
       ('W'),
       ('R'),
       ('F');

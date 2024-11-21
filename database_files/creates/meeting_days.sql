CREATE TABLE meeting_days(
  meeting_days_id  INT,
  day_letter       CHAR(8),
  PRIMARY KEY (meeting_days_id, day_letter),
  FOREIGN KEY (day_letter) REFERENCES days (day_letter) ON DELETE RESTRICT
);

CREATE VIEW meeting_days_view AS
SELECT  meeting_days_id,
        GROUP_CONCAT(day_letter) AS schedule
FROM    meeting_days
GROUP BY meeting_days_id;



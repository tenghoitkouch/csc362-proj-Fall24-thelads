CREATE TABLE meeting_times(
  time_start   TIME,
  time_end     TIME,
  PRIMARY KEY (time_start, time_end)
);

CREATE VIEW meeting_times_view AS
SELECT  time_start,
        time_end
FROM    meeting_times;

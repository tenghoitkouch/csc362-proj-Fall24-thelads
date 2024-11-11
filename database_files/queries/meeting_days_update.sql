UPDATE meeting_days
SET    day_id = ?
WHERE  meeting_days_id = ?
       AND day_id = ?;

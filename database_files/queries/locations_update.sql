UPDATE locations
SET    room_capacity = ?
WHERE building_name = ?,
      AND room_number = ?;

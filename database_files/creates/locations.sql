CREATE TABLE locations (
  building_name  INT,
  room_number    INT,
  room_capacity  INT,
  PRIMARY KEY(building_name, room_number),
  FOREIGN KEY(building_name) REFERENCES buildings (building_name) ON DELETE SET NULL
);
CREATE VIEW locations_view (
  SELECT    building_name,
            room_number,
            room_capacity
  FROM      locations
  GROUP BY  building_name
  ORDER BY  building_name ASC
            room_number ASC
)

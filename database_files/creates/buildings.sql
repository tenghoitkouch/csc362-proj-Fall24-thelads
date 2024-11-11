CREATE TABLE buildings(
  building_name    VARCHAR(40),
  PRIMARY KEY (building_name)
);
CREATE VIEW buildings_view(
  SELECT building_name,
  FROM buildings
);

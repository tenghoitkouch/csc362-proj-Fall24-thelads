CREATE TABLE buildings (
  building_name    VARCHAR(64),
  PRIMARY KEY (building_name)
);

CREATE VIEW buildings_view AS
SELECT building_name
FROM buildings;

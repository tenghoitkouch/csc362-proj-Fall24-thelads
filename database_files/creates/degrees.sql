CREATE TABLE degrees (
  degree_id    INT AUTO_INCREMENT,
  degree_name  VARCHAR(75),
  PRIMARY KEY (degree_id)
)
CREATE VIEW degrees_view (
  SELECT  degree_id,
          degree_name
  FROM    degrees
  GROUP BY degree_id 
  ORDER BY degree_name ASC
)

CREATE TABLE terms (
  term_id          INT AUTO_INCREMENT,
  term_start_date  DATE,
  term_end_date    DATE,
  PRIMARY KEY (term_id)
);

CREATE VIEW terms_view AS
SELECT  term_id,
        term_start_date,
        term_end_date
FROM    terms;

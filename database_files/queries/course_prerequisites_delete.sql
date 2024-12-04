DELETE FROM course_prerequisites
WHERE  course_id = ?
  AND  prerequisite_id = ?;

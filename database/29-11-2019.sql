ALTER TABLE `student_points`
DROP FOREIGN KEY `student_points_users_id_fk`,
ADD FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `student_course`
DROP FOREIGN KEY `student_course_users_id_fk`,
ADD FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `course_lector`
DROP FOREIGN KEY `course_lector_users_id_fk`,
ADD FOREIGN KEY (`lector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `course`
DROP FOREIGN KEY `course_garant_id_fk`,
ADD FOREIGN KEY (`garant`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
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

ALTER TABLE `event_file`
DROP FOREIGN KEY `event_file_event_id_fk`,
ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `event_room`
DROP FOREIGN KEY `event_room_ibfk_1`,
ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `student_points`
DROP FOREIGN KEY `student_points_event_id_fk`,
ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `student_course`
DROP FOREIGN KEY `student_course_course_id_fk`,
ADD FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `event_room`
DROP FOREIGN KEY `event_room_ibfk_2`,
ADD FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `event_file`
DROP FOREIGN KEY `course_file_file_id_fk`,
ADD FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `event`
DROP FOREIGN KEY `event_ibfk_1`,
ADD FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `course_room`
DROP FOREIGN KEY `course_room_ibfk_1`,
ADD FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `course_room`
DROP FOREIGN KEY `course_room_ibfk_2`,
ADD FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `course_lector`
DROP FOREIGN KEY `course_lector_course_id_fk`,
ADD FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



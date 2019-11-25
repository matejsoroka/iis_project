<?php


namespace App\Model;

class StudentCourseModel extends BaseModel
{

    public $table = "student_course";

    public function isRegistered(int $courseId, int $student_id)
    {
        return $this->findItem(["course_id" => $courseId, "student_id" => $student_id]) ? true : false;
    }

    public function getPoints(int $studentId) : array
    {
        // this needs refactor or some better idea
        $courses = $this->fetchPairs(["student_id" => $studentId], "course_id", "course_id");
        $points = [];

        foreach ($courses as $course)
        {
            $events = $this->db->query("SELECT * FROM event WHERE course_id = ?", $course);
            $i = 0;
            foreach ($events as $event) {
                $row = $this->db->query("SELECT points FROM student_points WHERE student_id = ? AND event_id = ?", $studentId, $event->id);
                if ($row) {
                    $i += $row->fetch()["points"];
                }
            }
            $points[$course] = $i;
        }
        return $points;
    }

}
<?php


namespace App\Model;

class StudentCourseModel extends BaseModel
{

    public $table = "student_course";

    public function isRegistered(int $courseId, int $student_id)
    {
        return $this->findItem(["course_id" => $courseId, "student_id" => $student_id]) ? true : false;
    }

}
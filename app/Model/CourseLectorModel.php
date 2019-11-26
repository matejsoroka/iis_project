<?php


namespace App\Model;

class CourseLectorModel extends BaseModel
{

    public $table = "course_lector";

    public function isLector(int $userId, int $courseId) : bool
    {
        return $this->findItem(["lector_id" => $userId, "course_id" => $courseId]) ? true : false;
    }

}
<?php


namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class CourseModel extends BaseModel
{

    public function getCourses() : Selection
    {
        return $this->db->table("course");
    }

    public function getCourse(int $id) : ActiveRow
    {
        return $this->db->table("course")->where("id", $id)->fetch();
    }

    public function changeCourse(int $id, array $data) : int
    {
        return $this->db->table("course")->where("id", $id)->update($data);
    }

    public function add(array $values) : ActiveRow
    {
        return $this->db->table("course")->insert($values);
    }

}
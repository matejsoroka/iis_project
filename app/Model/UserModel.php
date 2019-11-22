<?php


namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserModel extends BaseModel
{

    public function getUsers() : Selection
    {
        $q = $this->db->table("users")->select("id, email, username, first_name, second_name, role");
        return $q;
    }

    public function getUser(int $id) : ActiveRow
    {
        $q = $this->db->table("users")->select("id, email, username, first_name, second_name, role")
            ->where("id", $id);
        return $q->fetch();
    }

    public function changeUser(int $id, array $data) : int
    {
        return $this->db->table("users")->where("id", $id)->update($data);
    }

}
<?php


namespace App\Model;

use Nette\Database\Table\ActiveRow;

class UserModel extends BaseModel
{

    public function getUsers() : array
    {
        $q = $this->db->table("users")->select("id, username, first_name, second_name, role");
        return $q->fetchAll();
    }

    public function getUser(int $id) : ActiveRow
    {
        $q = $this->db->table("users")->select("id, username, first_name, second_name, role")
            ->where("id", $id);
        return $q->fetch();
    }

}
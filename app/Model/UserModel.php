<?php


namespace App\Model;


use Nette\Database\Context;

class UserModel extends BaseModel
{

    public function getUsers() : array
    {
        $q = $this->db->table("users")->select("id, username, first_name, second_name, role");
        return $q->fetchAll();
    }

}
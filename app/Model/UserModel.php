<?php


namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserModel extends BaseModel
{

    public $table = "users";

    public function getUsers() : Selection
    {
        return $this->db->table("users")->select("id, email, username, name, surname, role");
    }

    public function getUser(int $id) : ActiveRow
    {
        $q = $this->db->table("users")->select("id, email, username, name, surname, role")
            ->where("id", $id);
        return $q->fetch();
    }

    public function changeUser(int $id, array $data) : int
    {
        return $this->edit($id, $data);
    }

    public function createLogin(string $name, string $surname)
    {
        $number = 0;
        $isLetter = 0;
        $letter = 'a';

        $surnameLength = strlen($surname);
        $users = $this->getUsers()->fetchAll();

        if ($surnameLength < 5) {
            $remainder = substr($name, 0, $surnameLength - strlen($name));
            $username = 'x'.lcfirst($surname).$remainder;
        } else {
            $username = 'x'.substr(lcfirst($surname), 0, 5);
        }

        $login = $username . '0' . ($number);
        foreach ($users as $user) {
            if (strcmp($user['username'], $login) == 0) {
                if (!$isLetter) $number++;

                if ($number > 99) {
                    $isLetter = 1;
                    $number = 0;
                }

                if ($isLetter) {
                    $number++;
                    if ($number > 9) {
                        $letter = chr(ord($letter) + 1);
                        $number = 0;
                    }
                }
            }

            if (!$isLetter) {
                if ($number < 9) {
                    $login = $username . '0' . ($number);
                } else {
                    $login = $username . ($number);
                }
            } else {
                $login = $username . $letter . ($number);
            }
        }

        return $login;
    }

}
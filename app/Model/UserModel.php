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

    public function getUsernames(string $substring) : array
    {
        $users = $this->db->table('users')->select("username")
            ->where('username LIKE ?', "%".$substring."%")->fetchAll();

        $names = [];
        foreach ($users as $user) {
            $names[] = $user->username;
        }

        return $names;
    }

    public function createLogin(string $name, string $surname)
    {
        $number = 0;
        $isLetter = 0;
        $letter = 'a';

        $surnameLength = strlen($surname);

        if ($surnameLength < 5) {
            $remainder = substr($name, 0, 5 - $surnameLength);
            $username = 'x'.lcfirst($surname).lcfirst($remainder);
        } else {
            $username = 'x'.substr(lcfirst($surname), 0, 5);
        }

        $users = $this->getUsernames($username);

        $login = $username . '0' . ($number);

        for ($i = 0; $i < count($users); $i++) {
            $key = array_search($login, $users);

            if ($key || ($key == 0 && !is_bool($key))) {
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
                if ($number < 10) {
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
<?php


namespace App\Model;


use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class EventModel extends BaseModel
{
    public $table = "event";

    public function getNextId()
    {
        $k = $this->db->table($this->table)->order('id DESC')->limit(1)->fetch();
        return $k ? $k->id + 1 : 1;
    }

    public function getEvents(array $where) : Selection
    {
        return $this->db->table($this->table)->where($where);
    }
}
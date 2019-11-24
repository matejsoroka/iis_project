<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class BaseModel
{

    /** @var Context */
    public $db;

    /** @var string Table name */
    public $table;

    public function __construct(Context $db) {
        $this->db = $db;
    }

    public function getItem($id) : ActiveRow
    {
        return $this->db->table($this->table)->where("id", $id)->fetch();
    }

    public function getTable() : Selection
    {
        return $this->db->table($this->table);
    }

    public function add(array $values) : ActiveRow
    {
        return $this->db->table($this->table)->insert($values);
    }

    public function edit(int $id, $values) : int
    {
        return $this->db->table($this->table)->where("id", $id)->update($values);
    }

}
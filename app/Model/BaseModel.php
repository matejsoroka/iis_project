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

    public function getItem(int $id) : ActiveRow
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

    public function edit(int $id, iterable $values) : int
    {
        return $this->db->table($this->table)->where("id", $id)->update($values);
    }

    public function multiEdit(array $ids, iterable $values) : int
    {
        return $this->db->table($this->table)->where(["id" => $ids])->update($values);
    }

    public function delete(array $where) : int
    {
        return $this->db->table($this->table)->where($where)->delete();
    }

    public function fetchPairs(array $where, string $key, string $value) : array
    {
        return $this->db->table($this->table)->where($where)->fetchPairs($key, $value);
    }

    public function getNextId()
    {
        $k = $this->db->table($this->table)->order('id DESC')->limit(1)->fetch();
        return $k ? $k->id + 1 : 1;
    }

    public function getItems(array $where) : Selection
    {
        return $this->db->table($this->table)->where($where);
    }

    public function findItem(array $where) {
        return $this->db->table($this->table)->where($where)->fetch();
    }

}
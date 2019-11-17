<?php


namespace App\Model;


use Nette\Database\Context;

class BaseModel
{

    /** @var Context */
    public $db;

    public function __construct(Context $db) {
        $this->db = $db;
    }

}
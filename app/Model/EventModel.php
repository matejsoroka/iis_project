<?php


namespace App\Model;


use Nette\Database\Table\Selection;
use Nette\Database\Context;

class EventModel extends BaseModel
{
    public $table = "event";

    /** @var EventFileModel */
    public $eventFileModel;

    /** @var FileModel */
    public $fileModel;

    public function __construct(Context $db, EventFileModel $eventFileModel, FileModel $fileModel)
    {
        parent::__construct($db);
        $this->eventFileModel = $eventFileModel;
        $this->fileModel = $fileModel;
    }

    public function getEvents(array $where) : Selection
    {
        return $this->db->table($this->table)->where($where);
    }

    public function addFiles(int $event_id, array $files) : void
    {
        $path = "upload/events/" . $event_id . "/";
        foreach ($files as $file) {
            $row = [
                "path" => $path.$file->getName(),
                "type" => $file->getContentType()
            ];
            $file->move(__DIR__ . "/../../www/" . $path . $file->name);
            $insert = $this->fileModel->add($row);
            $this->eventFileModel->add(["event_id" => $event_id, "file_id" => $insert->id]);
        }
    }


}
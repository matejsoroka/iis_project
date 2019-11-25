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
                "type" => $file->getContentType(),
                "name" => $file->getName()
            ];
            $file->move(__DIR__ . "/../../www/" . $path . $file->name);
            $insert = $this->fileModel->add($row);
            $this->eventFileModel->add(["event_id" => $event_id, "file_id" => $insert->id]);
        }
    }

    public function checkDate(string $date, string $time, int $eventId)
    {
        $weekDay = date('w', strtotime($date));

        $items = $this->db->table($this->table)->fetchAll();

        foreach ($items as $item) {
            if (!$eventId || $item->id != $eventId) {
                /* if event repeats weekly */
                if ((int)$item->repeat) {
                    /* check if it is not in same day of week */
                    if ($weekDay == date('w', strtotime($item->date))) {
                        /* check time */
                        if (strtotime($time) == strtotime($item->time)) {
                            return 1;
                        }
                    }
                } else {
                    if (strtotime($date) == $item->date->getTimestamp()) {
                        if (strtotime($time) == strtotime($item->time)) {
                            return 1;
                        }
                    }
                }
            }
        }

        return 0;
    }


}
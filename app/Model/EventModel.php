<?php


namespace App\Model;


use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Database\Context;

class EventModel extends BaseModel
{
    public $table = "event";

    /** @var EventFileModel */
    public $eventFileModel;

    /** @var FileModel */
    public $fileModel;

    /** @var EventRoomModel */
    public $eventRoomModel;

    public function __construct(Context $db, EventFileModel $eventFileModel, FileModel $fileModel, EventRoomModel $eventRoomModel)
    {
        parent::__construct($db);
        $this->eventFileModel = $eventFileModel;
        $this->fileModel = $fileModel;
        $this->eventRoomModel = $eventRoomModel;
    }

    public function getEvents(array $where) : Selection
    {
        return $this->db->table($this->table)->where($where);
    }

    public function getEvent(int $id) : ActiveRow
    {
        return $this->db->table($this->table)->where('id', $id)->fetch();
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

    public function checkDate(string $date, string $time_from, string $time_to, int $roomId, int $eventId)
    {
        $weekDay = date('w', strtotime($date));

        $rooms = $this->eventRoomModel->getEventsInRoom($roomId);

        $events = [];
        foreach ($rooms as  $room) {
            $events[] = $this->getEvent($room->event_id);
        }

        $isSame = false;
        foreach ($events as $event) {
            if (!$eventId || $event->id != $eventId) {
                /* if event repeats weekly */
                if ((int)$event->repeat) {
                    /* check if it is not in same day of week */
                    if ($weekDay == date('w', strtotime($event->date))) {
                        /* check time */
                        $isSame = true;
                    }
                } else {
                    if (strtotime($date) == $event->date->getTimestamp()) {
                        $isSame = true;
                    }
                }
            }

            if ($isSame) {
                if (strtotime($time_from) >= strtotime($event->time_from) && strtotime($time_to) <= strtotime($event->time_to)
                    || (strtotime($time_from) < strtotime($event->time_from) && strtotime($time_to) >= strtotime($event->time_from) && strtotime($time_to) <= strtotime($event->time_to))
                    || (strtotime($time_from) >= strtotime($event->time_from) && strtotime($time_from) <= strtotime($event->time_to) && strtotime($time_to) > strtotime($event->time_to))
                    || (strtotime($time_from) < strtotime($event->time_from) && strtotime($time_to) > strtotime($event->time_to))) {
                    return 1;
                }
            }
        }

        return 0;
    }


}
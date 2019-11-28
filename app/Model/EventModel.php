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

    /** @var RoomModel */
    public $roomModel;

    public function __construct(Context $db,
                                EventFileModel $eventFileModel,
                                FileModel $fileModel,
                                EventRoomModel $eventRoomModel,
                                RoomModel $roomModel)
    {
        parent::__construct($db);
        $this->eventFileModel = $eventFileModel;
        $this->fileModel = $fileModel;
        $this->eventRoomModel = $eventRoomModel;
        $this->roomModel = $roomModel;
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

    public function getSchedule(int $roomId, string $date) : string
    {
        $rooms = $this->eventRoomModel->getItems(['room_id' => $roomId])->fetchAll();

        $dateFrom = date('Y-m-d', strtotime("previous monday", strtotime($date)));
        $dateTo = date('Y-m-d', strtotime("this friday", strtotime($date)));

        $events = [];
        $allEvents = [];

        /* get all events in selected room */
        foreach ($rooms as $room) {
            $allEvents[] = $this->getItem($room->event_id);
            $event = $this->db->table($this->table)->where('`id` = ? AND `date` BETWEEN ? AND ?', $room->event_id, $dateFrom.' 00:00:00', $dateTo.' 23:59:59')->fetch();
            if ($event) {
                $events[] = $event;
            }
        }

        foreach ($allEvents as $event) {
            /* if event repeats every week it should be in schedule too */
            if ($event->repeat) {
                $events[] = $event;
            }
        }


        $schedule = [];
        $inArray = false;

        foreach ($events as $event) {
            /* weekday of event */
            $weekDay = (int)date('w', strtotime($event->date)) - 1;
            $from = (int)substr($event->time_from, 0, 2);

            if (substr($event->time_to, 3, 5) > '00') {
                $to = (int)substr($event->time_to, 0, 2) + 2;
            } else {
                $to = (int)substr($event->time_to, 0, 2) + 1;
            }


            if (!sizeof($schedule)) {
                while ($from != $to) {
                    $schedule[$weekDay][] = (string)$from . ':00';
                    $from++;
                }
            } else {
                foreach ($schedule as $key => $item) {
                    if ($weekDay == $key) {
                        while ($from != $to) {
                            array_push($item, (string)$from . ':00');
                            $from++;
                        }
                        sort($item);
                        $inArray = true;
                        $schedule[$key] = $item;
                    }
                }

                if (!$inArray) {
                    while ($from != $to) {
                        $schedule[$weekDay][] = (string)$from . ':00';
                        $from++;
                    }
                }

            }
        }

        return json_encode($schedule);

    }


}
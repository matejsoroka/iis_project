<?php


namespace App\Model;


use Nette\Database\Table\Selection;
use Nette\Database\Context;

class EventRoomModel extends BaseModel
{
    public $table = 'event_room';

    /** @var RoomModel */
    public $roomModel;

    public function __construct(Context $db, RoomModel $roomModel)
    {
        parent::__construct($db);
        $this->roomModel = $roomModel;
    }

    public function getAvailableSchedules(int $eventId = NULL) : array
    {
        $eventRoom = $this->db->table($this->table)->where('event_id', $eventId);

        $roomSchedules = [];

        if ($eventId) {
            foreach ($eventRoom as $room) {
                $roomSchedules[$room->room_id] = $this->roomModel->getTable()->where('id', $room->room_id)->fetch();
            }
        }

        return $roomSchedules;
    }
}
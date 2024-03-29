<?php


namespace App\Model;

use Nette\Database\Context;

class CourseRoomModel extends BaseModel
{

    /** @var RoomModel */
    public $roomModel;

    public $table = "course_room";

    public function __construct(Context $db, RoomModel $roomModel)
    {
        parent::__construct($db);
        $this->roomModel = $roomModel;
    }

    public function getCourseSchedule(int $courseId) : array
    {
        $courseRoom = $this->getTable()->where('course_id', $courseId);

        $roomSchedules = [];
        foreach ($courseRoom as $room) {
            $roomSchedules[$room->room_id] = $this->roomModel->getTable()->where('id', $room->room_id)->fetch();
        }

        return $roomSchedules;
    }

    public function getAvailableRooms(int $courseId) : array
    {
        return $this->db
            ->query('SELECT * FROM room WHERE id IN (SELECT room_id FROM course_room WHERE course_id = ?)', $courseId)
            ->fetchPairs('id', 'number');
    }


}
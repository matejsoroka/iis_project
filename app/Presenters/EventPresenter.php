<?php


namespace App\Presenters;


use App\Forms\EventFormFactory;
use App\Model\CourseModel;
use App\Model\CourseRoomModel;
use App\Model\RoomModel;
use App\Model\EventModel;
use Nette\Application\UI\Form;

final class EventPresenter extends BasePresenter
{
    /** @var EventModel */
    private $eventModel;

    /** @var EventFormFactory */
    private $eventFormFactory;

    /** @var RoomModel */
    private $roomModel;

    /** @var CourseRoomModel */
    private $courseRoomModel;

    /** @var CourseModel */
    public $courseModel;

    /** @persistent */
    private $id;

    /** @persistent */
    private $course_id;

    public function __construct(EventModel $eventModel, EventFormFactory $eventFormFactory, RoomModel $roomModel, CourseRoomModel $courseRoomModel)
    {
        $this->eventModel = $eventModel;
        $this->eventFormFactory = $eventFormFactory;
        $this->roomModel = $roomModel;
        $this->courseRoomModel = $courseRoomModel;
    }

    public function actionEdit(int $courseId, int $eventId = NULL)
    {
        $this->id = $eventId;
        $this->template->eventId = $eventId;
        $this->template->courseHours = [];
        $this->course_id = $courseId;

        $courseRoom = $this->courseRoomModel->getTable()->where('course_id', $courseId);

        $roomSchedules = [];
        foreach ($courseRoom->fetchAll() as $room) {
            $roomSchedules[$room->room_id] = $this->roomModel->getTable()->where('id', $room->room_id)->fetch();
        }

        $this->template->roomSchedules = $roomSchedules;
        $this->template->countRooms = $this->roomModel->getTable()->count();
        $this->template->countRooms = $this->roomModel->getTable()->count();

        if ($eventId) {
            $this['eventForm']->setDefaults($this->eventModel->getEvents(['id' => $eventId])->fetchAll());
        }
    }

    /**
     * Event form factory.
     * @param int $courseId
     * @return Form
     */
    protected function createComponentEventForm(): Form
    {
        return $this->eventFormFactory->create(function (): void {
            $this->redirect('Course:edit', $this->course_id);
        },  $this->course_id, $this->id);
    }

    public function handleSelectHours(array $hours, array $roomIds, array $newHours)
    {
        /* get max id of course */
        if ($this->id) {
            $id = $this->id;
        } else {
            $id = $this->eventModel->getNextId();
        }


        $courseHours = [];
        $counter = 0;
        $index = 0;
        $iterator = 0;

        if (count($newHours) > 0) {
            for ($i = 0; $i < count($roomIds); $i++) {
                for ($j = $iterator; $j < count($roomIds) * 5; $j++) {
                    $counter++;

                    if ($counter % 5 == 0 && $j > 0) {
                        //$this->roomModel->edit((int)$roomIds[$i], ['schedule' => json_encode($courseHours)]);
                        $courseHours = [];
                        $index = 0;
                        $iterator = $counter;

                        break;
                    } else {
                        if (isset($hours[$j])) {
                            $courseHours[$index] = $hours[$j];
                        }
                        $index++;
                    }

                }
            }

            $this->eventModel->edit($id, ['schedule' => json_encode($newHours)]);
        }

    }

    public function handleChangeRoom(array $roomIds)
    {
        if (count($roomIds) > 0) {

            $rooms = [];
            foreach ($roomIds as $id) {
                $rooms[$id] = $this->roomModel->getItem($id);
            }

            $this->template->roomSchedules = $rooms;
            $this->redrawControl('scheduleSnippet');

        } else {
            $this->template->courseHours = [];
            $this->redrawControl('scheduleSnippet');
        }
    }
}
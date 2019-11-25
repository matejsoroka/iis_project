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
    /** @var EventModel @inject */
    public $eventModel;

    /** @var EventFormFactory @inject */
    public $eventFormFactory;

    /** @var RoomModel @inject */
    public $roomModel;

    /** @var CourseRoomModel @inject */
    public $courseRoomModel;

    /** @var CourseModel @inject */
    public $courseModel;

    /** @persistent */
    private $id;

    /** @persistent */
    private $course_id;

    public function renderEdit(int $courseId, int $eventId = null)
    {
        $this->template->courseHours = [];
        $this->template->eventId = $eventId;
        $this->template->roomSchedules = $this->courseRoomModel->getCourseSchedule($courseId);
        $this->template->countRooms = $this->roomModel->getTable()->count();
    }

    public function actionEdit(int $courseId, int $eventId = null)
    {
        $this->id = $eventId;
        $this->course_id = $courseId;
    }

    public function renderDetail(int $eventId)
    {
        $this->template->event = $this->eventModel->getItem($eventId);
    }

    /**
     * Event form factory.
     * @return Form
     */
    protected function createComponentEventForm(): Form
    {
        bdump($this->course_id);
        return $this->eventFormFactory->create(function (): void {
            $this->redirect('Course:edit', $this->course_id);
        },
        $this->course_id, $this->id ? $this->id : 0);
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
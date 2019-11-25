<?php


namespace App\Presenters;

use App\Forms\EventFormFactory;
use App\Model\CourseModel;
use App\Model\CourseRoomModel;

use App\Model\EventRoomModel;
use App\Model\EventFileModel;
use App\Model\RoomModel;
use App\Model\EventModel;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

final class EventPresenter extends BasePresenter
{
    /** @var EventModel @inject */
    public $eventModel;

    /** @var EventFormFactory @inject */
    public $eventFormFactory;

    /** @var RoomModel @inject */
    public $roomModel;

    /** @var EventFileModel @inject */
    public $eventFileModel;

    /** @var CourseRoomModel @inject */
    public $courseRoomModel;

    /** @var CourseModel @inject */
    public $courseModel;

    /** @var EventRoomModel @inject */
    public $eventRoomModel;

    /** @persistent */
    private $id;

    /** @persistent */
    private $course_id;

    /** @persistent */
    private $schedules;

    public function renderEdit(int $courseId, int $eventId = null)
    {
        $this->template->courseHours = [];
        $this->template->eventId = $eventId;
        $this->template->countRooms = $this->roomModel->getTable()->count();
        $this->template->roomSchedules = $this->schedules;
        $this->template->files = $this->eventFileModel->getItems(["event_id" => $eventId]);
    }

    public function actionEdit(int $courseId, int $eventId = null)
    {
        $this->id = $eventId;
        $this->course_id = $courseId;
        $this->schedules = $this->eventRoomModel->getAvailableSchedules($eventId);
    }

    public function renderDetail(int $eventId)
    {
        $this->template->event = $this->eventModel->getItem($eventId);
        $this->template->files = $this->eventFileModel->getItems(["event_id" => $eventId]);
    }

    /**
     * Event form factory.
     * @return Form
     */
    protected function createComponentEventForm(): Form
    {
        return $this->eventFormFactory->create(function (): void {
            $this->redirect('Course:edit', $this->course_id);
        },
        $this->course_id, $this->id ? $this->id : 0, $this->eventTypes);
    }

    public function handleChangeRoom(array $roomIds)
    {
        if (count($roomIds) > 0) {
            $rooms = [];
            foreach ($roomIds as $id) {
                $rooms[$id] = $this->roomModel->getItem($id);
            }

            $this->schedules = $rooms;
            $this->redrawControl('scheduleSnippet');

        } else {
            $this->schedules  = [];
            $this->redrawControl('scheduleSnippet');
        }
    }

    public function handleDeleteFile(int $id)
    {
        $f = $this->eventFileModel->getItem($id);
        unlink(__DIR__ . "/../../www/upload/events/" . $f->event . "/" . $f->file->name);
        $f->delete();
        if ($this->isAjax()) {
            $this->redrawControl("eventFiles");
        } else {
            $this->redirect("this");
        }
    }
}
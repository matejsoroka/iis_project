<?php

namespace App\Presenters;

use App\Forms\EventFormFactory;
use App\Forms\EventPointsFormFactory;
use App\Model\CourseLectorModel;
use App\Model\CourseModel;
use App\Model\CourseRoomModel;

use App\Model\EventRoomModel;
use App\Model\EventFileModel;
use App\Model\RoomModel;
use App\Model\EventModel;
use App\Model\StudentCourseModel;
use App\Model\StudentPointsModel;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

final class EventPresenter extends BasePresenter
{
    /** @var EventModel @inject */
    public $eventModel;

    /** @var EventFormFactory @inject */
    public $eventFormFactory;

    /** @var EventPointsFormFactory @inject */
    public $eventPointsFormFactory;

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

    /** @var StudentCourseModel @inject */
    public $studentCourseModel;

    /** @var CourseLectorModel @inject */
    public $courseLectorModel;

    /** @var StudentPointsModel @inject */
    public $studentPointsModel;

    /** @persistent */
    private $id;

    /** @var ActiveRow */
    private $course;

    /** @var ActiveRow */
    private $event;

    /** @persistent */
    private $course_id;

    /** @persistent */
    private $schedules;

    public function renderEdit(int $courseId, int $eventId = null)
    {
        $this->template->courseHours = [];
        $this->template->eventId = $eventId;
        $this->template->countRooms = $this->roomModel->getTable()->count();
        $this->template->files = $this->eventFileModel->getItems(["event_id" => $eventId]);
        $this->template->registered = $this->studentCourseModel->getItems(["course_id" => $courseId]);
        $this->template->roomSchedules = $this->schedules;
    }

    public function actionEdit(int $courseId, int $eventId = null)
    {

        if (!$this->user->isAllowed("EditCourseStatus")) {
            if (!$this->courseLectorModel->isLector($this->user->getId(), $courseId)) {
                $this->flashMessage("Nemáte oprávnenie pre správu kurzu", "warning");
                $this->redirect("Course:");
            }
        }

        $this->id = $eventId;
        $this->course_id = $courseId;
        $this->course = $this->courseModel->getItem($courseId);

        if ($eventId) {
            $this->event = $this->eventModel->getItem($eventId);
            $this->schedules = $this->eventRoomModel->getAvailableSchedules($eventId);
        } else {
            $this->schedules = [];
        }
    }

    public function renderDetail(int $eventId)
    {
        $this->template->event = $this->eventModel->getItem($eventId);
        $this->template->files = $this->eventFileModel->getItems(["event_id" => $eventId]);
        $points = $this->studentPointsModel->findItem(["student_id" => $this->user->getId(), "event_id" => $eventId]);
        $this->template->studentPoints = $points ? $points->points : "-";
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

    /**
     * Event form factory.
     * @return Form
     */
    protected function createComponentEventPointsForm(): Form
    {
        return $this->eventPointsFormFactory->create(function (): void {
            $this->redirect('Course:edit', $this->course_id);
        },
        $this->course_id, $this->id, $this->event->points);
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
            $this->redrawControl('scheduleButton');

        } else {
            $this->schedules  = [];
            $this->redrawControl('scheduleSnippet');
            $this->redrawControl('scheduleButton');
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
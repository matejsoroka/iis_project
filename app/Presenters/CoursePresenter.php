<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\CourseFormFactory;
use App\Model\CourseLectorModel;
use App\Model\CourseModel;
use App\Model\DuplicateNameException;
use App\Model\EventModel;
use App\Model\RoomModel;
use App\Model\CourseRoomModel;
use App\Model\StudentCourseModel;
use App\Model\StudentPointsModel;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\UniqueConstraintViolationException;
use Ublaboo\DataGrid\DataGrid;

final class CoursePresenter extends BasePresenter
{

    /** @var CourseModel @inject */
    public $courseModel;

    /** @var EventModel */
    public $eventModel;

    /** @var StudentCourseModel @inject */
    public $studentCourseModel;

    /** @var CourseLectorModel @inject */
    public $courseLectorModel;

    /** @var CourseFormFactory */
    private $courseFormFactory;

    /** @var StudentPointsModel @inject */
    public $studentPointsModel;

    /** @var ActiveRow */
    public $course;

    /** @persistent */
    private $id;

    public function __construct(CourseFormFactory $courseFormFactory, EventModel $eventModel)
    {
        $this->courseFormFactory = $courseFormFactory;
        $this->eventModel = $eventModel;
    }

    public function actionDefault() : void
    {
        $this->hasGrid = true;
    }

    public function renderDetail(int $id) : void
    {
        $this->template->course = $this->courseModel->getItem($id);

        $courseEvents = $this->eventModel->getEvents(['course_id' => $id]);
        $this->template->events = $courseEvents;

        $registered = false;
        if ($this->user->isLoggedIn()) {
            $registered = $this->studentCourseModel->isRegistered($id, $this->getUser()->getId());
        }
        $this->template->registered = $registered;
        if ($registered) {
            $this->template->points = $this->studentPointsModel->getStudentEventPoints($this->user->getId(), $courseEvents->fetchAll());
        }
    }

    public function actionEdit(int $id = 0)
    {
        $this->id = $id;

        if (!$this->user->isAllowed("EditCourseStatus")) {
            if ($id) {
                if (!($this->courseLectorModel->isLector($this->user->getId(), $id) || $this->course->garant == $this->user->getId())) { // De Morgan
                    $this->flashMessage("Nemáte oprávnenie pre správu kurzu", "warning");
                    $this->redirect("Course:");
                }
            }
        }
    }

    public function renderEdit(int $id = 0)
    {
        $this->hasGrid = true;
        $this->template->courseId = $id;
        if ($id) {
            $this->template->course = $this->courseModel->getItem($id);
            $this->template->events = $this->eventModel->getEvents(['course_id' => $this->id]);
        }
    }

    /**
     * Course form factory.
     */
    protected function createComponentCourseForm(): Form
    {
        return $this->courseFormFactory->create(function (): void {
            $this->redirect('Course:edit', $this->presenter->getParameter("id"));
        }, $this->user->id, (int) $this->presenter->getParameter("id"));
    }

    public function createComponentRegisterGrid() : DataGrid
    {
        $grid = new DataGrid($this, "registerGrid");

        $grid->setRefreshUrl(FALSE);

        $grid->setDataSource($this->studentCourseModel->getStudents($this->id));

        $grid->addColumnText("username", "Login")
            ->setRenderer(function ($row) {
               return $row->student->username;
            });

        $grid->addColumnText("name", "Meno")
                ->setRenderer(function ($row) {
                return $row->student->name;
            });

        $grid->addColumnText("surname", "Priezvisko")
            ->setRenderer(function ($row) {
                return $row->student->surname;
            });

        $grid->addColumnStatus("status", "Status")
                ->addOption(0, "Neschválený")
                ->endOption()
                ->addOption(1, "Schválený")
                ->endOption()
                ->onChange[] = function($id, $value): void {
                    $this->studentCourseModel->edit((int) $id, ["status" => $value]);
                    $this->redirect("this");
                };

//        $grid->addGroupAction('Schváliť vybraných študentov')->onSelect[] = [$this, 'multiAccept'];

        return $grid;

    }

    public function createComponentCourseGrid() : DataGrid
    {
        $grid = new DataGrid($this, "courseGrid");

        if ($this->user->isAllowed("Course:edit"))
            $grid->setDataSource($this->courseModel->getItems([]));
        else
            $grid->setDataSource($this->courseModel->getItems(["status" => 1]));

        $grid->addColumnLink("shortcut", "Skratka", 'detail', 'shortcut', ['id'])
            ->setFilterText();

        $grid->addColumnLink("name", "Názov", 'detail', 'name', ['id'])
            ->setFilterText();

        if ($this->user->isAllowed("ShowCourseStatus")) {          // Manage course status

            if ($this->user->isAllowed("EditCourseStatus")) {      // Show select box for edit

                $grid->addColumnStatus('status', 'Stav')
                    ->setCaret(FALSE)
                    ->addOption(0, $this->courseStatuses[0])
                    ->endOption()
                    ->addOption(1, $this->courseStatuses[1])
                    ->endOption()
                    ->onChange[] = function($id, $value): void {
                        $this->columnEdit($id, "status", $value);
                    };
                    $grid->addFilterSelect('status', 'Stav', $this->courseStatuses);

            } else {                                                       // Show only text

                $grid->addColumnText("status", "Stav")
                    ->setRenderer(function($item) : string {
                        return $this->courseStatuses[$item->status];
                    })
                    ->setFilterSelect($this->courseStatuses);

            }

        }

        $grid->addColumnText("price", "Cena")
            ->setFilterText();

        $grid->addColumnText("type", "Typ")
            ->setRenderer(function($item) : string {
                return $this->types[$item->type];
            })
            ->setFilterSelect($this->types);

        $grid->addColumnText("tags", "Tagy")
            ->setFilterText();

        if ($this->user->isAllowed("Course:edit")) {
            $grid->addAction('edit', '', 'edit')
                ->setIcon('pencil')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed("Course:register")) {
            $grid->addAction('register', 'Registrovať sa', 'Register!')
                ->setIcon('check')
                ->setClass('btn btn-xs btn-success');
        }

        $grid->addFilterSelect("role", "Rola", $this->roles);

        return $grid;
    }

    public function columnEdit(string $id, string $param, string $value) : void
    {
        $this->courseModel->edit((int) $id, [$param => $value]);
        if ($this->isAjax()) {
            $this["courseGrid"]->redrawItem($id);
        } else {
            $this->redirect('this');
        }
    }

    public function multiAccept(array $ids) : void
    {
        $this->studentCourseModel->multiEdit($ids, ["status" => 1]);
        $this->redirect('this');
    }

    public function handleRegister(int $id)
    {
        try {
            $this->studentCourseModel->add(["student_id" => $this->user->getId(), "course_id" => $id]);
            $this->flashMessage("Úspešne ste sa registrovali, čakajte na schválenie od garanta", "success");
        } catch (UniqueConstraintViolationException $e) {
            $this->flashMessage("Už ste registrovaný", "warning");
        }
    }
}

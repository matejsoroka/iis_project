<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\CourseFormFactory;
use App\Model\CourseModel;
use App\Model\RoomModel;
use App\Model\CourseRoomModel;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

final class CoursePresenter extends BasePresenter
{

    /** @var CourseModel @inject */
    public $courseModel;

    /** @var CourseFormFactory */
    private $courseFormFactory;

    /** @var RoomModel */
    private $roomModel;

    /** @var CourseRoomModel */
    private $courseRoomModel;

    /** @persistent */
    private $id;

    public function __construct(CourseFormFactory $courseFormFactory, RoomModel $roomModel, CourseRoomModel $courseRoomModel)
    {
        $this->courseFormFactory = $courseFormFactory;
        $this->roomModel = $roomModel;
        $this->courseRoomModel = $courseRoomModel;
    }

    public function actionDefault() : void
    {
        $this->hasGrid = true;
    }

    public function renderDetail(int $id) : void
    {
        $this->template->course = $this->courseModel->getItem($id);
    }

    public function actionEdit(int $id = NULL)
    {
        $this->id = $id;
        $this->template->courseId = $id;
        $this->template->courseHours = [];

        if ($id) {
            $courseRoom = $this->courseRoomModel->getTable()->where('course_id', $id);

            $roomSchedules = [];
            foreach ($courseRoom->fetchAll() as $room) {
                $roomSchedules[$room->room_id] = $this->roomModel->getTable()->where('id', $room->room_id)->fetch();
            }

            $this->template->roomSchedules = $roomSchedules;
            $this->template->countRooms = $this->roomModel->getTable()->count();
        }
    }

    /**
     * Course form factory.
     */
    protected function createComponentCourseForm(): Form
    {
        return $this->courseFormFactory->create(function (): void {
            $this->redirect('Course:');
        }, $this->user->id, (int) $this->presenter->getParameter("id"));
    }

    public function createComponentCourseGrid() : DataGrid
    {
        $grid = new DataGrid($this, "courseGrid");

        $grid->setDataSource($this->courseModel->getTable());

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

    public function handleSelectHours(array $hours, array $roomIds, array $newHours)
    {
        /* get max id of course */
        if ($this->id) {
            $id = $this->id;
        } else {
            $id = $this->courseModel->db
                ->query('SELECT * FROM course WHERE id = (SELECT MAX(id) FROM course)')->fetch()->id + 1;
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
                        $this->roomModel->edit((int)$roomIds[$i], ['schedule' => json_encode($courseHours)]);
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

            $this->courseModel->edit($id, ['schedule' => json_encode($newHours)]);
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

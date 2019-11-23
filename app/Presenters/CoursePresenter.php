<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\CourseFormFactory;
use App\Model\CourseModel;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

final class CoursePresenter extends BasePresenter
{

    /** @var CourseModel @inject */
    public $courseModel;

    /** @var CourseFormFactory */
    private $courseFormFactory;

    public function __construct(CourseFormFactory $courseFormFactory)
    {
        $this->courseFormFactory = $courseFormFactory;
    }

    public function actionDefault() : void
    {
        $this->hasGrid = true;
    }

    public function renderDetail(int $id) : void
    {
        $this->template->course = $this->courseModel->getCourse($id);
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

        $grid->addAction('edit', '', 'edit')
            ->setIcon('pencil')
            ->setClass('btn btn-xs btn-primary');

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
}

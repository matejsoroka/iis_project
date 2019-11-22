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

    public function actionDefault() : void
    {
        $this->hasGrid = true;
    }

    public function renderDetail(int $id)
    {
        $this->template->course = $this->courseModel->getCourse($id);
    }

    public function __construct(CourseFormFactory $courseFormFactory)
    {
        $this->courseFormFactory = $courseFormFactory;
    }

    /**
     * Sign-in form factory.
     */
    protected function createComponentCourseForm(): Form
    {
        return $this->courseFormFactory->create(function (): void {
            $this->redirect('Course:');
        }, (string) $this->user->id);
    }

    public function createComponentCourseGrid() : DataGrid
    {
        $grid = new DataGrid($this, "courseGrid");

        $grid->setDataSource($this->courseModel->getCourses());

        $grid->addColumnLink("shortcut", "Skratka", 'detail', 'shortcut', ['id'])
            ->setFilterText();

        $grid->addColumnLink("name", "Názov", 'detail', 'name', ['id'])
            ->setFilterText();

        $grid->addColumnText("price", "Cena")
            ->setFilterText();

        $grid->addColumnText("type", "Typ")
            ->setFilterText();

        $grid->addColumnText("tags", "Tagy")
            ->setFilterText();

        $roles = [
            "",
            "admin" => "Admin",
            "leader" => "Vedúci",
            "garant" => "Garant",
            "lector" => "Lektor",
            "student" => "Študent",
        ];

        $grid->addFilterSelect("role", "Rola", $roles);

        return $grid;
    }
}

<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\RoomFormFactory;
use App\Model\CourseModel;
use App\Model\RoomModel;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

final class RoomPresenter extends BasePresenter
{

    /** @var RoomModel @inject */
    public $roomModel;

    /** @var RoomFormFactory */
    private $roomFormFactory;

    public function __construct(RoomFormFactory $courseFormFactory)
    {
        $this->roomFormFactory = $courseFormFactory;
    }

    public function actionDefault() : void
    {
        $this->hasGrid = true;
    }

    public function renderDetail(int $id) : void
    {
        $this->template->room = $this->roomModel->getItem($id);
    }

    public function renderEdit(int $id = 0)
    {
        $this->template->roomId = $id;
    }

    /**
     * Room form factory.
     */
    protected function createComponentCourseForm(): Form
    {
        return $this->roomFormFactory->create(function (): void {
            $this->redirect('Room:');
        }, (int) $this->presenter->getParameter("id"));
    }

    public function createComponentRoomGrid() : DataGrid
    {
        $grid = new DataGrid($this, "roomGrid");

        $grid->setDataSource($this->roomModel->getTable());

        $grid->addColumnText("address", "Adresa")
            ->setFilterText();

        $grid->addColumnText("number", "Číslo miestnosti")
            ->setFilterText();

        $grid->addColumnText("type", "Typ miestnosti")
            ->setFilterText();

        $grid->addColumnText("capacity", "Kapacita")
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

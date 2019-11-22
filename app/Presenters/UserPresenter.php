<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Model\UserModel;
use Nette\ComponentModel\IComponent;
use Ublaboo\DataGrid\DataGrid;

final class UserPresenter extends BasePresenter
{

    /** @var UserModel @inject */
    public $userModel;

    public function actionDefault()
    {
        $this->hasGrid = true;
    }

	public function renderEdit(int $id): void
	{
	    // Do not want to rename $user variable in template, so I call it row
		$this->template->row = $this->userModel->getUser($id);
	}

	public function createComponentUserGrid() : DataGrid
    {
        $grid = new DataGrid();

        $grid->setDataSource($this->userModel->getUsers());

        $grid->addColumnText("first_name", "Meno")->setFilterText();
        $grid->addColumnText("second_name", "Priezvisko")->setFilterText();
        $grid->addColumnText("username", "Login")->setFilterText();
        $grid->addColumnText("role", "Rola");

        $grid->addFilterSelect("role", "Rola", ["", "admin", "leader", "garant", "lector", "student"]);

        $grid->addAction("edit", "", ":edit")
            ->setIcon("pencil")
            ->setClass("btn-sm btn-primary");

        return $grid;
    }

}

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

	public function renderDefault(): void
	{
		$this->template->anyVariable = 'any value';
	}

	public function createComponentUserGrid() : DataGrid
    {
        $grid = new DataGrid();

        $grid->setDataSource($this->userModel->getUsers());

        $grid->addColumnText("first_name", "Meno")->setFilterText();
        $grid->addColumnText("second_name", "Priezvisko")->setFilterText();
        $grid->addColumnText("username", "Login")->setFilterText();
        $grid->addColumnText("role", "Rola")->setFilterText();

        return $grid;
    }
}

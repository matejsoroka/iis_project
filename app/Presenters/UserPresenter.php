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
        $grid = new DataGrid($this, "userGrid");

        $grid->setDataSource($this->userModel->getUsers());

        $grid->addColumnText("first_name", "Meno")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "first_name", $value); die();
            })
            ->setFilterText();

        $grid->addColumnText("second_name", "Priezvisko")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "second_name", $value); die();
            })
            ->setFilterText();

        $grid->addColumnText("username", "Login")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "username", $value); die();
            })
            ->setFilterText();

        $grid->addColumnText("email", "Email")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "email", $value); die();
            })
            ->setFilterText();

        $grid->addColumnStatus('role', 'Rola')
            ->setCaret(FALSE)
            ->addOption("admin", "Admin")
            ->endOption()
            ->addOption("leader", "Vedúci")
            ->endOption()
            ->addOption("garant", "Garant")
            ->endOption()
            ->addOption("lector", "Lektor")
            ->endOption()
            ->addOption("student", "Študent")
            ->endOption()
            ->onChange[] = [$this, 'changeRole'];

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

    public function columnEdit(string $id, string $param, string $value) : void
    {
        $this->userModel->changeUser((int) $id, [$param => $value]);
        if ($this->isAjax()) {
            $this["userGrid"]->redrawItem($id);
        } else {
            $this->redirect('this');
        }
    }

}

<?php

declare(strict_types=1);

namespace App\Presenters;


use App\Forms\SignUpFormFactory;
use App\Model\UserModel;
use Nette\ComponentModel\IComponent;
use Ublaboo\DataGrid\DataGrid;

final class UserPresenter extends BasePresenter
{

    /** @var UserModel @inject */
    public $userModel;

    /** @var SignUpFormFactory @inject */
    public $signUpFormFactory;

    /** @persistent */
    private $id = 0;

    public function actionDefault() : void
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

        $grid->addColumnText("name", "Meno")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "name", $value); die();
            })
            ->setFilterText();

        $grid->addColumnText("surname", "Priezvisko")
            ->setEditableCallback(function($id, $value): void {
                $this->columnEdit($id, "surname", $value); die();
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
            ->addOption("registered", "Registrovaný")
            ->endOption()
            ->onChange[] = function($id, $value): void {
                $this->columnEdit($id, "role", $value);
            };

        $roles = [
            "",
            "admin" => "Admin",
            "leader" => "Vedúci",
            "garant" => "Garant",
            "lector" => "Lektor",
            "student" => "Študent",
        ];

        $grid->addFilterSelect("role", "Rola", $roles);

        if ($this->user->isAllowed("User:edit")) {
            $grid->addAction('edit', '', 'edit')
                ->setIcon('pencil')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed("User:delete")) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setClass('btn btn-xs btn-danger');
        }

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

    public function actionEdit(int $id)
    {
        if ($id != $this->user->id) {
            $this->flashMessage("Nemáte oprávnenie upravovať iného používateľa", "warning");
            $this->redirect('User:default');
        } else {
            $this->id = $id;
        }
    }

    public function createComponentUserForm()
    {
        return $this->signUpFormFactory->create(function (): void {
            $this->redirect('User:');
        }, $this->id);
    }

    public function handleDelete(int $id)
    {
        $user = $this->userModel->getItem($id);

        if ($this->user->id == $id || $user->role == 'admin') {
            $this->flashMessage("Nemôžete zmazať tohto používateľa", "warning");
            $this->redirect('User:');

        } else {
            $this->userModel->delete(['id' => $id]);
            $this->flashMessage("Používateľ úspešne vymazaný", "success");
            $this->redirect('User:');
        }
    }

}

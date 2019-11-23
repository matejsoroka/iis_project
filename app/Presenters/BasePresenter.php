<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\ForbiddenRequestException;


/**
 * Base presenter for all application presenters.
 */

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var bool If presenter has grid,
     * it is for including styles and scripts
     */
    public $hasGrid = false;

    /**
     * @var array of types of courses
     */
    public $types = ["" => "", "Povinný", "Volitelný", "Špeciálny"];

    /**
     * @var array of roles
     */
    public $roles = [
        "",
        "admin" => "Admin",
        "leader" => "Vedúci",
        "garant" => "Garant",
        "lector" => "Lektor",
        "student" => "Študent",
    ];

    /**
     * @var array of course statuses
     */
    public $courseStatuses = ["" => "", "Neschválený", "Schválený"];

    protected function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isAllowed($this->getName() . ":" . $this->getAction())) {
            $this->flashMessage("Neopravnený prístup", "warning");
            $this->redirect("Homepage:");
        }
    }
}

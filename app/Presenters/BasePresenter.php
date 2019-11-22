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

    protected function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isAllowed($this->getName() . ":" . $this->getAction())) {
            $this->flashMessage("Neopravnený prístup", "warning");
            $this->redirect("Homepage:");
        }
    }
}

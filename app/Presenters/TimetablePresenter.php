<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StudentCourseModel;
use App\Model\StudentPointsModel;

final class TimetablePresenter extends BasePresenter
{

    /** @var StudentCourseModel @inject */
    public $studentCourses;

    /** @var StudentPointsModel @inject */
    public $studentPointsModel;

    public function renderDefault()
    {
        $this->template->courses = $this->studentCourses->getItems(["student_id" => $this->user->getId()]);
        $this->template->points = $this->studentCourses->getPoints($this->user->getId());
    }
}

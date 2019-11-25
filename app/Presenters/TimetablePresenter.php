<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StudentCourseModel;

final class TimetablePresenter extends BasePresenter
{

    /** @var StudentCourseModel @inject */
    public $studentCourses;

    public function renderDefault()
    {
        $this->template->courses = $this->studentCourses->getItems(["student_id" => $this->user->getId()]);
    }
}

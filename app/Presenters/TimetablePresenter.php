<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StudentCourseModel;
use App\Model\StudentPointsModel;
use App\Model\EventModel;

final class TimetablePresenter extends BasePresenter
{

    /** @var StudentCourseModel @inject */
    public $studentCourses;

    /** @var StudentPointsModel @inject */
    public $studentPointsModel;
    /** @var EventModel @inject */
    public $eventModel;

    public function renderDefault()
    {   $this->eventModel->updateSchedule(1, '15:30:00', '16:50:00', '21.11.2019');
        $this->template->courses = $this->studentCourses->getItems(["student_id" => $this->user->getId()]);
        $this->template->points = $this->studentCourses->getPoints($this->user->getId());
    }
}

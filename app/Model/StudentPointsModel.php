<?php


namespace App\Model;

use Nette\Database\Context;

class StudentPointsModel extends BaseModel
{

    public $table = "student_points";

    public function getStudentEventPoints(int $studentId, array $events)
    {
        $ids = [];
        foreach($events as $event) $ids[] = $event->id;
        return $this->fetchPairs(["student_id" => $studentId, "event_id" => $ids], "event_id", "points");
    }

}
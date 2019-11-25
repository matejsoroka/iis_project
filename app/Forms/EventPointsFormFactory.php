<?php


namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

class EventPointsFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var Model\EventModel */
    private $eventModel;

    /** @var Model\StudentPointsModel */
    private $studentPointsModel;

    /** @var Model\StudentCourseModel */
    private $studentCourseModel;

    public function __construct(FormFactory $factory, Model\StudentCourseModel $studentCourseModel, Model\StudentPointsModel $studentPointsModel)
    {
        $this->factory = $factory;
        $this->studentPointsModel = $studentPointsModel;
        $this->studentCourseModel = $studentCourseModel;
    }

    public function create(callable $onSuccess, int $course_id, int $event_id, int $max): Form
    {
        $form = $this->factory->create();

        foreach ($this->studentCourseModel->getItems(["course_id" => $course_id]) as $student) {
            $student = $student->student;
            $form->addInteger($student->id, $student->username)
                ->addRule($form::RANGE, 'Hodnota bodov musí byť medzi %d a %d.', [0, $max]);
        }

        $form->setDefaults($this->studentPointsModel->fetchPairs(["event_id" => $event_id], "student_id", "points"));

        $form->addSubmit('save');

        $form->onSuccess[] = function (Form $form, array $values) use ($onSuccess, $event_id): void {
            $this->studentPointsModel->delete(["event_id" => $event_id]);
            foreach ($values as $student_id => $points) {
                $this->studentPointsModel->add([
                    "student_id" => $student_id, "event_id" => $event_id, "points" => $points
                ]);
            }
        };

        return $form;
    }

}
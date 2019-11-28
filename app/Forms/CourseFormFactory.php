<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


final class CourseFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\CourseModel */
	private $courseModel;

    /** @var Model\RoomModel */
    private $roomModel;

    /** @var Model\UserModel */
    private $userModel;

    /** @var Model\CourseRoomModel */
    private $courseRoomModel;

    /** @var Model\CourseLectorModel */
    private $courseLectorModel;


	public function __construct(
	    FormFactory $factory,
        Model\CourseModel $courseModel,
        Model\RoomModel $roomModel,
        Model\CourseRoomModel $courseRoomModel,
        Model\UserModel $userModel,
        Model\CourseLectorModel $courseLectorModel
    )
	{
		$this->factory = $factory;
		$this->courseModel = $courseModel;
		$this->roomModel = $roomModel;
		$this->courseRoomModel = $courseRoomModel;
		$this->userModel = $userModel;
		$this->courseLectorModel = $courseLectorModel;
	}


	public function create(callable $onSuccess, int $creator, int $course_id): Form
	{
		$form = $this->factory->create();

        $form->addText('shortcut', 'Skratka*')
            ->setRequired('Prosím, zadajte skratku');

        $form->addText('name', 'Názov*')
            ->setRequired('Prosím, zadajte názov');

        $form->addTextArea('description', 'Popis');

        $form->addText('price', 'Cena');

        $form->addSelect('type', "Typ", [0 => "-- Typ --", 1 => "Povinný", 2 => "Voliteľný"]);

        $form->addText('tags', 'Tagy');

        $form->addHidden("garant", (string) $creator);

        $form->addMultiSelect('lectors', "Lektori", $this->userModel->fetchPairs(["role" => ["lector", "garant", "leader", "admin"]], "id", "username"));

        $rooms = $this->roomModel->getTable()->fetchPairs('id', 'number');
        $form->addMultiSelect('room', "Miestnosť", $rooms);

        if ($course_id) {

            $selected = $this->courseRoomModel->fetchPairs(['course_id' => $course_id], 'id', 'room_id');

            $form->setDefaults($this->courseModel->getItem($course_id));
            $form->setDefaults(['room' => $selected]);

            $lectors = ($this->courseLectorModel->fetchPairs(["course_id" => $course_id], "lector_id", "lector_id"));
            $form->setDefaults(['lectors' => $lectors]);
        }

        $form->addHidden("id", (string) $course_id);

		$form->addSubmit('send');

		$form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
			try {
			    if ($values["id"]) {
                    $this->courseRoomModel->delete(['course_id' => $values['id']]);
                    foreach ($values['room'] as $room) {
                        $array = ['room_id' => $room, 'course_id' => $values['id']];
                        $this->courseRoomModel->add($array);
                    }
                    unset($values['room']);
                    $this->courseLectorModel->delete(['course_id' => $values["id"]]);
                    foreach ($values['lectors'] as $lector) {
                        $array = ['lector_id' => $lector, 'course_id' => $values["id"]];
                        $this->courseLectorModel->add($array);
                    }
                    unset($values['lectors']);
                    $this->courseModel->edit((int) $values["id"], $values);
                } else {
                    $values["status"] = 0; // needs to by approved by leader
                    $insert = $this->courseModel->add($values);
                    $course_id = $insert->id;
                    foreach ($values['room'] as $room) {
                        $array = ['room_id' => $room, 'course_id' => $course_id];
                        $this->courseRoomModel->add($array);
                    }
                    foreach ($values['lectors'] as $lector) {
                        $array = ['lector_id' => $lector, 'course_id' => $course_id];
                        $this->courseLectorModel->add($array);
                    }
                }
			} catch (Model\DuplicateNameException $e) {
				$form['shortcut']->addError('Skratka je už zabraná, použite prosím inú');
				return;
			}
            $form->getPresenter()->flashMessage('Uložené.', 'success');
			$onSuccess();
		};

		return $form;
	}
}

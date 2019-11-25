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

    /** @var Model\CourseRoomModel */
    private $courseRoomModel;


	public function __construct(FormFactory $factory, Model\CourseModel $courseModel, Model\RoomModel $roomModel, Model\CourseRoomModel $courseRoomModel)
	{
		$this->factory = $factory;
		$this->courseModel = $courseModel;
		$this->roomModel = $roomModel;
		$this->courseRoomModel = $courseRoomModel;
	}


	public function create(callable $onSuccess, int $creator, int $course_id): Form
	{
		$form = $this->factory->create();

        $form->addText('shortcut', 'Skratka')
            ->setRequired('Prosím, zadajte skratku');

        $form->addText('name', 'Názov')
            ->setRequired('Prosím, zadajte názov');

        $form->addText('price', 'Cena');

        $form->addSelect('type', "Typ", [0 => "-- Typ --", 1 => "Povinný", 2 => "Voliteľný"]);

        $form->addText('tags', 'Tagy');

        $form->addHidden("garant", (string) $creator);

        if ($course_id) {
            $rooms = $this->roomModel->getTable()->fetchPairs('id', 'number');
            $form->addMultiSelect('room', "Miestnosť", $rooms);

            $selected = $this->courseRoomModel->fetchPairs(['course_id' => $course_id], 'id', 'room_id');
            $form->setDefaults(['room' => $selected]);
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
                    $this->courseModel->edit((int) $values["id"], $values);
                } else {
                    $values["status"] = 0; // needs to by approved by leader
                    $this->courseModel->add($values);

                }
			} catch (Model\DuplicateNameException $e) {
				$form['shortcut']->addError('Skratka je už zabraná, použite prosím inú');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}

<?php


namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

class EventFormFactory
{
    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var Model\EventModel */
    private $eventModel;

    /** @var Model\RoomModel */
    private $roomModel;

    /** @var Model\CourseRoomModel */
    private $courseRoomModel;

    public function __construct(FormFactory $factory, Model\RoomModel $roomModel, Model\EventModel $eventModel, Model\CourseRoomModel $courseRoomModel)
    {
        $this->factory = $factory;
        $this->roomModel = $roomModel;
        $this->eventModel = $eventModel;
        $this->courseRoomModel = $courseRoomModel;
    }

    public function create(callable $onSuccess, int $course_id, int $event_id): Form
    {
        $form = $this->factory->create();

        $form->addText('title', 'Názov')
            ->setRequired('Prosím, zadajte názov');

        $form->addText('description', 'Popis')
            ->setRequired('Prosím, zadajte popis');

        $form->addText('type', 'Typ')
            ->setRequired('Prosím, zadajte typ termínu');

        $form->addText('date', 'Dátum')
            ->setRequired('Prosím, zadajte dátum');

        $rooms = $this->roomModel->getTable()->fetchPairs('id', 'number');
        $form->addMultiSelect('room', "Miestnosť", $rooms);

        $selected = $this->courseRoomModel->fetchPairs(['course_id' => $course_id], 'id', 'room_id');
        $form->setDefaults(['room' => $selected]);

        $form->addHidden("id", (string)$event_id);

        $form->addHidden("course_id", (string)$course_id);

        $form->addSubmit('save');

        $form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
            try {
                unset($values['room']);
                if ($values['id']) {
                    $this->eventModel->edit((int)$values["id"], $values);

                } else {
                    $this->eventModel->add($values);

                }
            } catch (Model\DuplicateNameException $e) {
                $form['title']->addError('Názov je už použitý, vytvorte nový, prosím.');
                return;
            }
            $onSuccess();
        };

        $form->onError[] = [$this, 'processError'];

        return $form;
    }

    /**
     * @param \Nette\Application\UI\Form $form
     */
    public function processError($form)
    {
        $presenter = $form->getPresenter();
        if ($presenter->isAjax()) {
            $presenter->redrawControl('eventFormSnippet');
        }

    }
}
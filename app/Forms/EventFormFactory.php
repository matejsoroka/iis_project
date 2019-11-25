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

    /** @var Model\EventRoomModel */
    private $eventRoomModel;

    /** @var Model\CourseRoomModel */
    private $courseRoomModel;

    public function __construct(FormFactory $factory,
                                Model\RoomModel $roomModel,
                                Model\EventModel $eventModel,
                                Model\CourseRoomModel $courseRoomModel,
                                Model\EventRoomModel $eventRoomModel)
    {
        $this->factory = $factory;
        $this->roomModel = $roomModel;
        $this->eventModel = $eventModel;
        $this->courseRoomModel = $courseRoomModel;
        $this->eventRoomModel = $eventRoomModel;
    }

    public function create(callable $onSuccess, int $course_id, int $event_id, array $eventTypes): Form
    {
        $form = $this->factory->create();

        $form->addText('title', 'Názov')
            ->setRequired('Prosím, zadajte názov');

        $form->addTextArea('description', 'Popis')
            ->setRequired('Prosím, zadajte popis');

        $form->addSelect('type', 'Typ', $eventTypes)
            ->setRequired('Prosím, zadajte typ termínu');

        $form->addText('date', 'Dátum')
            ->setRequired('Prosím, zadajte dátum');

        $form->addInteger('points', 'Max. bodov')
            ->setRequired('Prosím, zadajte maximum bodov');

        $form->addMultiUpload('files', 'Pridať súbory');

        $availableRooms = $this->courseRoomModel->getAvailableRooms($course_id);
        $form->addMultiSelect('room', "Miestnosť", $availableRooms);

        $form->addHidden("id", (string) $event_id);

        $form->addHidden("course_id", (string) $course_id);

        if ($event_id) {
            $form->setDefaults($this->eventModel->getItem($event_id));

            $selected = $this->eventRoomModel->fetchPairs(['event_id' => $event_id], 'id', 'room_id');
            $form->setDefaults(['room' => $selected]);
        }

        $form->addSubmit('save');

        $form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
            try {

                if ($values["files"]) {
                    $this->eventModel->addFiles((int) $values["id"], $values["files"]);
                }

                unset($values["files"]);

                if ($values['id']) {
                    $this->eventRoomModel->delete(['event_id' => $values['id']]);

                    foreach ($values['room'] as $room) {
                        $array = ['room_id' => $room, 'event_id' => $values['id']];
                        $this->eventRoomModel->add($array);
                    }

                    unset($values['room']);
                    $this->eventModel->edit((int) $values["id"], $values);
                } else {
                    unset($values['room']);
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
     * @param Form $form
     */
    public function processError(Form $form)
    {
        $presenter = $form->getPresenter();
        if ($presenter->isAjax()) {
            $presenter->redrawControl('eventFormSnippet');
        }

    }
}
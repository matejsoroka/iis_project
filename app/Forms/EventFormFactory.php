<?php


namespace App\Forms;

use App\Model;
use Exception;
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

        $form->addText('title', 'Názov*')
            ->setRequired('Prosím, zadajte názov');

        $form->addTextArea('description', 'Popis*')
            ->setRequired('Prosím, zadajte popis');

        $form->addSelect('type', 'Typ*', $eventTypes)
            ->setRequired('Prosím, zadajte typ termínu');

        $form->addText('date', 'Dátum*')
            ->setRequired('Prosím, zadajte dátum')
            ->setHtmlAttribute('placeholder', 'DD/MM/YYYY')
            ->setType('date');;

        $form->addText('time_from', 'Čas od*')
            ->setRequired('Prosím, zadajte čas od')
            ->setHtmlAttribute('placeholder', '00:00')
            ->setType('time');

        $form->addText('time_to', 'Čas do*')
            ->setRequired('Prosím, zadajte čas do')
            ->setHtmlAttribute('placeholder', '00:00')
            ->setType('time');

        $form->addInteger('points', 'Max. bodov*')
            ->setRequired('Prosím, zadajte maximum bodov');

        $form->addMultiUpload('files', 'Pridať súbory');

        $form->addCheckbox('repeat', 'Opakovať týždenne');

        $availableRooms = $this->courseRoomModel->getAvailableRooms($course_id);
        $form->addMultiSelect('room', "Miestnosť", $availableRooms);

        $form->addHidden("id", (string) $event_id);

        $form->addHidden("course_id", (string) $course_id);

        $form->addHidden("change", 0)
            ->setHtmlAttribute('id', 'changeFlag');

        if ($event_id) {
            $form->setDefaults($this->eventModel->getItem($event_id));

            /* change format of date */
            $date = date("Y-m-d", strtotime($this->eventModel->getItem($event_id)->date));
            $form->setDefaults(['date' => $date]);

            $selected = $this->eventRoomModel->fetchPairs(['event_id' => $event_id], 'id', 'room_id');
            $form->setDefaults(['room' => $selected]);
        }

        $form->addSubmit('save');

        $form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
            try {
                if ($values["files"]) {
                    $this->eventModel->addFiles((int) $values["id"], $values["files"]);
                }

                /* check if date was changed */
                if ((int)$values['change']) {
                    /* time from cannot be smaller than time from */
                    if (strtotime($values['time_from']) >= strtotime($values['time_to'])) {
                        $form['time_to']->addError('Čas do nemôže byť rovnaký alebo menší ako čas od');
                        return;
                    }

                    foreach ($values['room'] as $roomId) {
                        /* check date */
                        $result = $this->eventModel->checkDate($values['date'], $values['time_from'], $values['time_to'], $roomId, $values['id']);
                        if ($result) {
                            $form['date']->addError('Udalosť v tomto dátume a čase už existuje.');
                            return;
                        }
                    }
               }

                unset($values["files"]);
                unset($values["change"]);

                if ($values['id']) {
                    $this->eventRoomModel->delete(['event_id' => $values['id']]);

                    foreach ($values['room'] as $room) {
                        $array = ['room_id' => $room, 'event_id' => $values['id']];
                        $this->eventRoomModel->add($array);
                    }

                    unset($values['room']);
                    $this->eventModel->edit((int) $values["id"], $values);
                } else {
                    $nextId = $this->eventModel->getNextId();

                    $rooms = $values['room'];

                    unset($values['room']);
                    $this->eventModel->add($values);

                    if ($rooms) {
                        foreach ($rooms as $room) {
                            $array = ['room_id' => $room, 'event_id' => $nextId];
                            $this->eventRoomModel->add($array);
                        }
                    }

                }
            } catch (Model\DuplicateNameException $e) {
                $form['title']->addError('Názov je už použitý, vytvorte nový, prosím.');
                return;
            }

            $form->getPresenter()->flashMessage('Uložené.', 'success');
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
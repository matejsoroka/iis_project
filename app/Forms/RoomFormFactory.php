<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class RoomFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\RoomModel */
	private $roomModel;


	public function __construct(FormFactory $factory, Model\RoomModel $roomModel)
	{
		$this->factory = $factory;
		$this->roomModel = $roomModel;
	}


	public function create(callable $onSuccess, int $room_id): Form
	{
		$form = $this->factory->create();

        $form->addText('address', 'Adresa')
            ->setRequired('Prosím, zadajte adresu, kde sa miestnosť nachádza');

        $form->addText('number', 'Číslo miestnosti')
            ->setRequired('Prosím, zadajte číslo miestnosti');

        $form->addText('type', "Typ")
            ->setRequired('Prosím, vyberte typ miestnosti');

        $form->addInteger('capacity', 'Kapacita')
            ->setRequired('Prosím, zadajte kapacitu miestnosti');

        $form->addHidden("id", (string) $room_id);

		$form->addSubmit('send');

		$form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
			try {
			    if ($values["id"]) {
                    $this->roomModel->edit((int) $values["id"], $values);
                } else {
                    $this->roomModel->add($values);
                }
			} catch (Model\DuplicateNameException $e) {
				$form['number']->addError('Číslo dverí na danej adrese už existuje');
				return;
			}
			$onSuccess();
		};

		if ($room_id) {
		    $form->setDefaults($this->roomModel->getItem($room_id));
        }

		return $form;
	}
}

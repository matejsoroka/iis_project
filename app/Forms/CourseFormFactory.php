<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


final class CourseFormFactory
{
	use Nette\SmartObject;

	private const PASSWORD_MIN_LENGTH = 7;

	/** @var FormFactory */
	private $factory;

	/** @var Model\CourseModel */
	private $courseModel;


	public function __construct(FormFactory $factory, Model\CourseModel $courseModel)
	{
		$this->factory = $factory;
		$this->courseModel = $courseModel;
	}


	public function create(callable $onSuccess, string $creator): Form
	{
		$form = $this->factory->create();

        $form->addText('shortcut', 'Skratka')
            ->setRequired('Prosím zadajte skratku');

        $form->addText('name', 'Nazov')
            ->setRequired('Prosím zadajte názov');

        $form->addText('price', 'Cena');

        $form->addSelect('type', "Typ", [0 => "= Typ =", 1 => "Povinný", 2 => "Volitelný"]);

        $form->addText('tags', 'Tagy');

        $form->addHidden("garant", $creator);

		$form->addSubmit('send');

		$form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
			try {
				$this->courseModel->add($values);
			} catch (Model\DuplicateNameException $e) {
				$form['shortcut']->addError('Skratka je už zabraná, použite prosím inú');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}

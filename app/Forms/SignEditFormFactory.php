<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


final class SignEditFormFactory
{
	use Nette\SmartObject;

	private const PASSWORD_MIN_LENGTH = 7;

	/** @var FormFactory */
	private $factory;

	/** @var Model\UserManager */
	private $userManager;


	public function __construct(FormFactory $factory, Model\UserManager $userManager)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

        $form->addText('email', 'Email:')
            ->setRequired('Prosím zadajte váš email');

        $form->addPassword('first_name', 'Meno:')
            ->setRequired('Prosím zadajte vaše meno.');

        $form->addPassword('second_name', 'Priezvisko:')
            ->setRequired('Prosím zadajte vaše priezvisko.');

		$form->addPassword('password', 'Create a password:')
			->setOption('description', sprintf('aspoň %d znakov', self::PASSWORD_MIN_LENGTH))
			->setRequired('Prosim zadajte heslo')
			->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

		$form->addSubmit('send', 'Sign up');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->userManager->add($values->username, $values->email, $values->password);
			} catch (Model\DuplicateNameException $e) {
				$form['email']->addError('Username is already taken.');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}

<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


final class SignInFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;


	public function __construct(FormFactory $factory, User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addText('username', 'Login:')
			->setRequired('Prosím, zadajte váš login');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím, zadajte vaše heslo');

		$form->addCheckbox('remember', 'Zapamätať si');

		$form->addSubmit('send', 'Prihlásit sa')
            ->setHtmlAttribute('id', 'btn-submit');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '1 day');
				$this->user->login($values->username, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError('The username or password you entered is incorrect.');
				return;
			}
            $form->getPresenter()->flashMessage('Prilásenie prebehlo úspešne.', 'success');
			$onSuccess();
		};

		return $form;
	}
}

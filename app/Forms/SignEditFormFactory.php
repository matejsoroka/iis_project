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

    /** @var Model\UserModel */
    private $userModel;

    private $id;

    public function __construct(FormFactory $factory, Model\UserModel $userModel)
    {
        $this->factory = $factory;
        $this->userModel = $userModel;
    }


    public function create(callable $onSuccess, int $id): Form
    {
        $this->id = $id;

        $form = $this->factory->create();

        $form->addText('username', 'Login:')
            ->setHtmlAttribute('disabled', 'disabled');

        $form->addText('email', 'Email:*')
            ->setRequired('Prosím, zadajte Váš email');

        $form->addPassword('name', 'Meno:*')
            ->setRequired('Prosím, zadajte Vaše meno.');

        $form->addPassword('surname', 'Priezvisko:')
            ->setRequired('Prosím, zadajte Vaše priezvisko.');

        $form->addPassword('password', 'Create a password:')
            ->setOption('description', sprintf('*aspoň %d znakov', self::PASSWORD_MIN_LENGTH))
            ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', 'Uložiť')
            ->setHtmlAttribute('id', 'btn-submit');

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
            try {
                $this->userModel->edit($this->id, $values);
            } catch (Model\DuplicateNameException $e) {
                //$form['email']->addError('Email už existuje');
                return;
            }
            $form->getPresenter()->flashMessage('Uložené.', 'alert-success');
            $onSuccess();
        };

        return $form;
    }
}
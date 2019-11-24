<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


final class SignUpFormFactory
{
    use Nette\SmartObject;

    private const PASSWORD_MIN_LENGTH = 7;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;

    /** @var Model\UserModel */
    private $userModel;


    public function __construct(FormFactory $factory, Model\UserManager $userManager, Model\UserModel $userModel)
    {
        $this->factory = $factory;
        $this->userManager = $userManager;
        $this->userModel = $userModel;
    }


    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addText('email', 'Email:')
            ->setRequired('Prosím, zadajte váš email');

        $form->addText('name', 'Meno:')
            ->setRequired('Prosím, zadajte vaše meno.');

        $form->addText('surname', 'Priezvisko:')
            ->setRequired('Prosím, zadajte vaše priezvisko.');

        $form->addPassword('password', 'Create a password:')
            ->setOption('description', sprintf('*aspoň %d znakov', self::PASSWORD_MIN_LENGTH))
            ->setRequired('Prosim, zadajte heslo')
            ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', 'Zaregistrovať sa')
            ->setHtmlAttribute('id', 'btn-submit');

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
            try {
                $login = $this->userModel->createLogin($values->name, $values->surname);
                $this->userManager->add($login, $values->name, $values->surname, $values->email, $values->password);
            } catch (Model\DuplicateNameException $e) {
                //$form['email']->addError('Username is already taken.');
                return;
            }
            $form->getPresenter()->flashMessage('Registrácia prebehla úspešne.', 'alert-success');
            $onSuccess();
        };

        return $form;
    }
}
<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use App\Control\MailSender;
use Nette;
use Nette\Application\UI\Form;


final class SignUpFormFactory
{
    use Nette\SmartObject;

    private const PASSWORD_MIN_LENGTH = 6;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;

    /** @var Model\UserModel */
    private $userModel;

    /** @var MailSender */
    private $mailSender;

    private $id;

    public function __construct(FormFactory $factory, Model\UserManager $userManager, Model\UserModel $userModel, MailSender $mailSender)
    {
        $this->factory = $factory;
        $this->userManager = $userManager;
        $this->userModel = $userModel;
        $this->mailSender = $mailSender;
    }


    public function create(callable $onSuccess, int $id = 0): Form
    {
        $form = $this->factory->create();

        $this->id = $id;

        $form->addEmail('email', 'Email*')
            ->setRequired('Prosím, zadajte váš email');

        $form->addText('name', 'Meno*')
            ->setRequired('Prosím, zadajte vaše meno.');

        $form->addText('surname', 'Priezvisko*')
            ->setRequired('Prosím, zadajte vaše priezvisko.');

        if ($this->id) {
            $form->addPassword('password', 'Heslo:')
                ->setOption('description', sprintf('*aspoň %d znakov', self::PASSWORD_MIN_LENGTH))
                ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);
        } else {
            $form->addPassword('password', 'Heslo*')
                ->setOption('description', sprintf('*aspoň %d znakov', self::PASSWORD_MIN_LENGTH))
                ->setRequired('Prosim, zadajte heslo')
                ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);
        }

        $form->addPassword('checkPassword', 'Zadajte znovu heslo:')
            ->addConditionOn($form['password'], FORM::FILLED, TRUE)
            ->setRequired('Prosim, zadajte kontrolné heslo')
            ->addRule(Form::EQUAL, 'Zadané heslá sa nezhodujú!', $form['password']);;

        $form->addSubmit('send', 'Zaregistrovať sa')
            ->setHtmlAttribute('id', 'btn-submit');

        if ($this->id) {
            $form->setDefaults($this->userModel->getUser($this->id));
        }

        $form->onError[] = [$this, 'handleError'];

        $form->onSuccess[] = function (Form $form, array $values) use ($onSuccess): void {
            unset($values['checkPassword']);

            try {
                if ($this->id) {
                    $this->userManager->edit($this->id, $values['name'], $values['surname'], $values['email'], $values['password']);
                    $form->getPresenter()->flashMessage('Úpravy sú uložené.', 'success');
                } else {
                    $login = $this->userModel->createLogin($values['name'], $values['surname']);
                    $this->userManager->add($login, $values['name'], $values['surname'], $values['email'], $values['password']);

                    $this->mailSender->sendEmail($login, $values['email']);
                    $form->getPresenter()->flashMessage('Registrácia prebehla úspešne. Počkajte, prosím, na potvrdzujúci email.', 'success');
                }
            } catch (Model\DuplicateNameException $e) {
                $form['email']->addError('Email už existuje');
                return;
            } catch (Nette\Mail\SendException $e) {
                $form->getPresenter()->flashMessage('Z technických príčin nebol odoslaný email s potvrdením registrácie. Skúste to neskôr, prosím.', 'warning');
                return;
            }

            $onSuccess();
        };

        return $form;
    }

    public function handleError(Form $form)
    {
        $presenter = $form->getPresenter();
        if ($presenter->isAjax()) {
            $presenter->redrawControl('userFormSnippet');
        }
    }
}
<?php

namespace App\Control;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class MailSender
{
    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var ITemplateFactory */
    private $templateFactory;

    public function __construct(LinkGenerator $linkGenerator, ITemplateFactory $ITemplateFactory)
    {
       $this->templateFactory = $ITemplateFactory;
       $this->linkGenerator = $linkGenerator;
    }

    public function sendEmail(string $login, string $email): void
    {
        $template = $this->createTemplate();
        $params = array('login' => $login);
        $template = $template->renderToString(__DIR__  . '/../Presenters/templates/Contact/emailLogin.latte', $params);

        $mail = new Message;
        $mail->setFrom('FIT <iis@matejsoroka.com>')
            ->addTo($email)
            ->setSubject('Registrácia')
            ->setHtmlBody($template);

        $mailer = new SmtpMailer([
            'host' => 'smtp.websupport.sk',
            'username' => 'iis@matejsoroka.com',
            'password' => 'Vz6Nc&DC[b',
            'secure' => 'ssl'
        ]);

        $mailer->send($mail);
    }

    protected function createTemplate(): \Nette\Application\UI\ITemplate
    {
        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);

        return $template;
    }
}
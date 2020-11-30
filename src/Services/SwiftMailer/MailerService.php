<?php

namespace App\Services\SwiftMailer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailerService extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }
    /**
     * @param $token
     * @param $username
     * @param $template
     * @param $to
     */
    public function sendToken($resetToken, $lifeTime, $template, $to)
    {
        $message = (new \Swift_Message('Mail de confirmation'))
            ->setFrom('konate.djabal@baysim-ci.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'reset_password/' . $template,
                    [
                      'resetToken' => $resetToken,
                      'tokenLifetime' => $lifeTime
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendMail($fullname, $to, $phone, $subject, $message, $template)
    {
        $message = (new \Swift_Message('Envoi de mail de contact'))
            ->setFrom($to)
            ->setTo('mahazbindjabal@gmail.com')
            ->setBody(
                $this->renderView(
                    'emails/' . $template,
                    [
                        'fullname' => $fullname,
                        'phone' => $phone,
                        'subject' => $subject,
                        'message' => $message

                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
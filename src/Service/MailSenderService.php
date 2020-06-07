<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailSenderService
{
    public const FROM_ADDRESS = 'flash.back@mail.ru';

    private $mailer;
    private $twig;
    private $generator;

    public function __construct(TwigService $twig, MailerInterface $mailer, UrlGeneratorInterface $generator)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->generator = $generator;
    }

    public function registerConfirm(string $email, string $token) : void
    {
        $messageBody = $this->twig->render('mail/user/email.html.twig', ['url'=>
            'http://'.
            $_ENV['APP_HOST'].
            $this->generator->generate('registerConfirm', ['token' => $token])
        ]);

        $email = (new Email())
            ->from(self::FROM_ADDRESS)
            ->to($email)
            ->subject('Регистрация в приложении Flash')
            ->text('Подтверждение регистрации')
            ->html($messageBody);
        $this->mailer->send($email);
    }

    public function resetPasswordConfirm(string $email, string $token) : void
    {
        $messageBody = $this->twig->render('mail/user/resetPassword.html.twig', ['url'=>
            'http://'.
            $_ENV['APP_HOST'].
            $this->generator->generate('resetPasswordConfirm', ['token' => $token])
        ]);

        $email = (new Email())
            ->from(self::FROM_ADDRESS)
            ->to($email)
            ->subject('Восстановление доступа в приложении Flash Back')
            ->text('Восстановление доступа в приложении Flash Back')
            ->html($messageBody);
        $this->mailer->send($email);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\User\UseCase\ResetPassword\Confirm;

use App\Domain\User\Entity\User;
use App\Domain\User\UserRepository;
use App\Domain\User\Service\PasswordEncoder;
use App\Service\FlushService;
use App\Service\MailService\BaseMessage;
use App\Service\MailService\MailBuilderService;
use App\Service\MailService\MailSenderService;
use App\Service\ValidateService;
use DateTimeImmutable;
use DomainException;

class Handler
{
    private $repository;
    private $flusher;
    private $validator;
    private $sender;
    private $builder;

    public function __construct(
        UserRepository $repository,
        ValidateService $validator,
        FlushService $flusher,
        MailSenderService $sender,
        MailBuilderService $builder
    ) {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->validator = $validator;
        $this->sender = $sender;
        $this->builder = $builder;
    }

    public function handle(Command $command): void
    {
        $this->validator->validate($command);

        /** @var User $user */
        if (!$user = $this->repository->findByConfirmToken($command->token)) {
            throw new DomainException('Incorrect or confirmed token.');
        }

        $user->confirmResetPassword(new DateTimeImmutable());
        $this->flusher->flush();

        $message = BaseMessage::getDefaultMessage(
            $user->getEmail(),
            'Успешная смена проля в приложении Flash',
            'Смена пароля',
            $this->builder
                ->setParam('url',  '123123123')
                ->build('mail/user/register.html.twig')
        );

        $this->sender->send($message);
    }
}

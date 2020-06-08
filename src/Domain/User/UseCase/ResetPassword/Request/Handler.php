<?php

declare(strict_types=1);

namespace App\Domain\User\UseCase\ResetPassword\Request;

use App\Domain\User\Entity\User;
use App\Domain\User\UserRepository;
use App\Domain\User\Service\TokenService;
use App\Service\FlushService;
use App\Service\MailSenderService;
use App\Service\ValidateService;
use DateTimeImmutable;

class Handler
{
    private $flusher;
    private $repository;
    private $tokenizer;
    private $sender;
    private $validator;

    public function __construct(
        ValidateService $validator,
        FlushService $flusher,
        UserRepository $repository,
        TokenService $tokenizer,
        MailSenderService $sender
    ) {
        $this->flusher = $flusher;
        $this->repository = $repository;
        $this->tokenizer = $tokenizer;
        $this->sender = $sender;
        $this->validator = $validator;
    }

    public function handle(Command $command): void
    {
        $this->validator->validate($command);
        /** @var User $user */
        $user = $this->repository->getByEmail($command->email);
        $user->requestResetPassword(
            $this->tokenizer->generate(),
            new DateTimeImmutable(),
            $command->password
        );

        $this->flusher->flush();
        $this->sender->resetPasswordConfirm($user->getEmail(), $user->getConfirmToken()->getToken());
    }
}
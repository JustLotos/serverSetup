<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Register;

use App\Model\User\Entity\Types\ConfirmTokenDTO;
use App\Model\User\UserRepository;
use App\Service\FlushService;
use App\Service\ValidateService;
use DateTimeImmutable;
use DomainException;

class ConfirmHandler
{
    private $repository;
    private $flusher;
    private $validator;

    public function __construct(UserRepository $repository, FlushService $flusher, ValidateService $validator)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->validator = $validator;
    }

    public function handle(ConfirmTokenDTO $token): void
    {
        $this->validator->validate($token);
        if (!$user = $this->repository->findByConfirmToken($token->getToken())) {
            throw new DomainException('Incorrect or confirmed token.');
        }
        $user->confirmRegister(new DateTimeImmutable());
        $this->flusher->flush();
    }
}
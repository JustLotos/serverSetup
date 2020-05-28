<?php

declare(strict_types=1);

namespace App\Model\Core\UseCase\Card\DiscreteRepeat;

use App\Model\Core\Entity\Card\Card;
use App\Model\Core\Repository\CardRepository;
use App\Model\Core\Service\AnswerMangerService\AnswerManagerService;
use App\Model\Flusher;
use App\Service\FlushService;
use App\Service\ValidateService;

class Handler
{
    private $flusher;
    private $validator;
    private $repository;
    private $manger;

    public function __construct(
        ValidateService $validator,
        AnswerManagerService $manger,
        FlushService $flusher,
        CardRepository $repository
    ) {
        $this->flusher = $flusher;
        $this->validator = $validator;
        $this->repository = $repository;
        $this->manger = $manger;
    }

    public function handle(Card $card, Command $command): Card
    {
        $this->validator->validate($command);

        $answer = new DiscreteAnswer(
            $command->date,
            $command->time,
            $command->status
        );

        $interval = $this->manger->getRepeatInterval(
            $card->getRepeat(),
            $card->getDeck()->getSettings(),
            $answer
        );

        $card->getRepeat()->update($answer, $interval);

        $this->flusher->flush();

        return $card;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Flash\UseCase\Deck\Create;

use App\Domain\Flash\Entity\Deck\Deck;
use App\Domain\Flash\Entity\Deck\DeckDTO;
use App\Domain\Flash\Entity\Deck\Types\Settings;
use App\Domain\Flash\Entity\Learner\Learner;
use App\Domain\Flash\Repository\DeckRepository;
use App\Domain\Flusher;
use App\Service\FlushService;
use App\Service\ValidateService;
use DateTimeImmutable;

class Handler
{
    private $flusher;
    private $validator;
    private $repository;

    public function __construct(
        ValidateService $validator,
        FlushService $flusher,
        DeckRepository $repository
    ) {
        $this->flusher = $flusher;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function handle(Command $deckDTO, Learner $learner): Deck
    {
        $this->validator->validate($deckDTO, [DeckDTO::POST]);

        $settings = new Settings(
            $deckDTO->baseInterval,
            $deckDTO->limitRepeat,
            $deckDTO->limitLearning,
            $deckDTO->difficultyIndex
        );

        $deck = new Deck(
            $learner,
            $deckDTO->name,
            $settings,
            new DateTimeImmutable(),
            $deckDTO->description
        );

        $this->repository->add($deck);
        $this->flusher->flush();

        return $deck;
    }
}
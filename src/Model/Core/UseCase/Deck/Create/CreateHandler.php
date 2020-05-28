<?php

declare(strict_types=1);

namespace App\Model\Core\UseCase\Deck\Create;

use App\Model\Core\Entity\Deck\Deck;
use App\Model\Core\Entity\Deck\DeckDTO;
use App\Model\Core\Entity\Deck\Types\Settings;
use App\Model\Core\Entity\Learner\Learner;
use App\Model\Core\Repository\DeckRepository;
use App\Model\Flusher;
use App\Service\FlushService;
use App\Service\ValidateService;
use DateTimeImmutable;

class CreateHandler
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

    public function handle(DeckDTO $deckDTO, Learner $learner): Deck
    {
        $this->validator->validate($deckDTO, [DeckDTO::POST]);

        $settings = new Settings(
            $deckDTO->baseInterval,
            $deckDTO->limitRepeat,
            $deckDTO->limitLearning,
            $deckDTO->difficultyIndex,
            $deckDTO->modifierIndex
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
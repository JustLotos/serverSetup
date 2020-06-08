<?php

declare(strict_types=1);

namespace App\Domain\Flash\UseCase\Card\Create;

use App\Domain\Flash\Entity\Card\Card;
use App\Domain\Flash\Entity\Card\CardDTO;
use App\Domain\Flash\Entity\Card\Types\Record;
use App\Domain\Flash\Entity\Card\Types\Repeat;
use App\Domain\Flash\Entity\Deck\Deck;
use App\Domain\Flash\Repository\CardRepository;
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
        CardRepository $repository
    ) {
        $this->flusher = $flusher;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function handle(Command $cardDTO, Deck $deck): Card
    {
        $this->validator->validate($cardDTO, [CardDTO::CREATE]);
        $front = Record::createFrontSide($cardDTO->frontSide[0]->content);
        $back  = Record::createBackSide($cardDTO->backSide[0]->content);
        $repeat = new Repeat(new DateTimeImmutable(), $deck->getSettings()->getBaseInterval());
        $card = Card::create($deck, $cardDTO->name, $front, $back, $repeat, new DateTimeImmutable());

        $this->repository->add($card);
        $this->flusher->flush();
        return $card;
    }
}
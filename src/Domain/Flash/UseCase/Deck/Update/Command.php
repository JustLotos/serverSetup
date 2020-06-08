<?php

declare(strict_types=1);

namespace App\Domain\Flash\UseCase\Deck\Update;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(allowEmptyString=false, min="1",  max="255")
     * @Serializer\Type(name="string")
     */
    public $name;

    /**
     * @Assert\Length(allowEmptyString=true, min="1",  max="255", charset="UTF-8")
     * @Serializer\Type(name="string")
     */
    public $description;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="10000")
     * @Serializer\Type(name="integer")
     */
    public $limitRepeat;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="10000")
     * @Serializer\Type(name="integer")
     */
    public $limitLearning;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="10000")
     * @Serializer\Type(name="double")
     */
    public $difficultyIndex;


    /**
     * @Assert\NotBlank()
     * @Serializer\Type(name="DateInterval")
     */
    public $baseInterval;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type(name="DateInterval")
     */
    public $minTime;
}
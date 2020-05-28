<?php

declare(strict_types=1);

namespace App\Model\User\Entity\Types;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use App\Model\User\Entity\User;

/**
 * @ORM\Embeddable
 */
class Name
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({User::GROUP_DETAILS, USER::GROUP_SIMPLE})
     */
    private $first;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({User::GROUP_DETAILS, USER::GROUP_SIMPLE})
     */
    private $last;

    public function __construct(string $first, string $last = null)
    {
        $this->first = $first;
        $this->last = $last;
    }

    public function getFirst(): string
    {
        return $this->first;
    }

    public function getLast(): string
    {
        return $this->last;
    }

    public function getFull(): string
    {
        return $this->first . ' ' . $this->last;
    }
}
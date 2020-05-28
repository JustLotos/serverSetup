<?php

declare(strict_types=1);

namespace App\Model\User\Entity;

use App\Model\User\Entity\Types\Id;
use App\Model\User\Entity\Types\Name;
use App\Model\User\Entity\Types\Role;
use App\Model\User\Entity\Types\ConfirmToken;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\UserInterface;
use DomainException;
use DateTimeImmutable;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_users", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"email"}),
 *     @ORM\UniqueConstraint(columns={"confirm_token_token"})
 * })
 */
class User implements UserInterface
{
    public const STATUS_WAIT = 'WAIT';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_BLOCKED = 'BLOCKED';

    /**
     * @var string
     * @ORM\Column(type="user_user_id")
     * @ORM\Id
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({User::GROUP_DETAILS, USER::GROUP_SIMPLE})
     */
    private $email;
    /**
     * @var string
     * @ORM\Column(type="string", name="password")
     */
    private $password;
    /**
     * @var string|null
     */
    private $plainPassword;
    /**
     * @var Name
     * @ORM\Embedded(class="App\Model\User\Entity\Types\Name")
     * @Serializer\Type(name="App\Model\User\Entity\Types\Name")
     * @Serializer\Groups({User::GROUP_DETAILS})
     */
    private $name;
    /**
     * @var string
     * @ORM\Column(type="string", name="temp", nullable=true)
     */
    private $temp;
    /**
     * @var ConfirmToken|null
     * @ORM\Embedded(class="App\Model\User\Entity\Types\ConfirmToken", columnPrefix="confirm_token_")
     */
    private $confirmToken;
    /**
     * @var string
     * @ORM\Column(type="string", length=16)
     * @Serializer\Groups({USER::GROUP_DETAILS})
     */
    private $status;
    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     * @Serializer\Groups({User::GROUP_DETAILS})
     */
    private $createdAt;
    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     * @Serializer\Groups({User::GROUP_DETAILS})
     */
    private $updatedAt;
    /**
     * @var Role
     * @ORM\Column(type="user_user_role", length=16)
     * @Serializer\Type(name="string")
     * @Serializer\Groups({User::GROUP_DETAILS, USER::GROUP_SIMPLE})
     */
    private $role;

    public const GROUP_DETAILS = 'GROUP_DETAILS';
    public const GROUP_SIMPLE = 'GROUP_SIMPLE';

    private function __construct(Id $id, DateTimeImmutable $date)
    {
        $this->id = $id;
        $this->createdAt = $date;
        $this->updatedAt = $date;
        $this->role = Role::user();
    }

    public static function registerByEmail(
        Id $id,
        DateTimeImmutable $date,
        Role $role,
        ConfirmToken $token,
        string $email,
        string $password
    ): self {
        if ($token->isExpiredTo($date)) {
            throw new DomainException('Reset token is expired.');
        }

        $user = new self($id, $date);
        $user->confirmToken = $token;
        $user->role = $role;
        $user->email = $email;
        $user->plainPassword = $password;
        $user->status = self::STATUS_WAIT;


        return $user;
    }

    public function requestRegisterConfirm(DateTimeImmutable $date): void
    {
        if (!$this->isWait()) {
            throw new DomainException('User is already confirmed.');
        }
        if (!$this->confirmToken) {
            throw new DomainException('Resetting is not requested.');
        }
        if ($this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Reset token is expired.');
        }
        $this->activate();
    }
    public function confirmRegister(DateTimeImmutable $date): void
    {
        if (!$this->isWait()) {
            throw new DomainException('User is already confirmed.');
        }
        if ($this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Confirm token is expired.');
        }

        $this->activate();
        $this->confirmToken = null;
    }

    public function requestResetPassword(ConfirmToken $token, DateTimeImmutable $date, string $password): void
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }
        if ($this->confirmToken && !$this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already requested.');
        }
        $this->block();
        $this->confirmToken = $token;
        $this->temp = $password;
    }
    public function confirmResetPassword(DateTimeImmutable $date): void
    {
        if (!$this->confirmToken) {
            throw new DomainException('Resetting is not requested.');
        }
        if ($this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Reset token is expired.');
        }
        $this->activate();
        $this->password = $this->temp;
    }

    public function requestEmailChanging(ConfirmToken $token, DateTimeImmutable $date, string $email): void
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }
        if ($this->confirmToken && !$this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already requested.');
        }
        if ($this->getEmail() === $email) {
            throw new DomainException('Email is already same.');
        }
        $this->block();
        $this->confirmToken = $token;
        $this->temp = $email;
    }
    public function confirmEmailChanging(DateTimeImmutable $date): void
    {
        if (!$this->confirmToken) {
            throw new DomainException('Changing is not requested.');
        }
        if ($this->confirmToken->isExpiredTo($date)) {
            throw new DomainException('Incorrect changing token.');
        }
        $this->activate();
        $this->email = $this->temp;
    }

    public function changeName(Name $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }
    public function edit(string $email, Name $name): void
    {
        $this->name = $name;
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }
    public function changeRole(Role $role): void
    {
        if ($this->role->isEqual($role)) {
            throw new DomainException('Role is already same.');
        }
        $this->role = $role;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('User is already active.');
        }
        $this->confirmToken = null;
        $this->temp = null;
        $this->status = self::STATUS_ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }
    public function block(): void
    {
        if ($this->isBlocked()) {
            throw new DomainException('User is already blocked.');
        }
        $this->status = self::STATUS_BLOCKED;
        $this->updatedAt = new DateTimeImmutable();
    }
    public function isWait(): bool
    {
         return $this->status === self::STATUS_WAIT;
    }
    public function isActive(): bool
    {
         return $this->status === self::STATUS_ACTIVE;
    }
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function getId(): Id
    {
        return $this->id;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getName(): ?Name
    {
        return $this->name;
    }

    public function getConfirmToken(): ?ConfirmToken
    {
        return $this->confirmToken;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getRoles(): array
    {
        return [$this->role->getName()];
    }
    public function getUsername(): string
    {
        return $this->getEmail();
    }
    public function eraseCredentials(): void
    {
        return;
    }
    public function getSalt(): void
    {
        return;
    }

}

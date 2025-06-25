<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Issue entity representing a bug report or feature request.
 */
#[ORM\Entity(repositoryClass: IssueRepository::class)]
class Issue
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Title is required.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title must be at least {{ limit }} characters long.',
        maxMessage: 'Title cannot be longer than {{ limit }} characters.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Description is required.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Description must be at least {{ limit }} characters long.'
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: 'Status is required.')]
    #[Assert\Choice(
        choices: [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_CLOSED],
        message: 'Please select a valid status.'
    )]
    private ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: 'Priority is required.')]
    #[Assert\Choice(
        choices: [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH],
        message: 'Please select a valid priority.'
    )]
    private ?string $priority = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'issues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Category is required.')]
    private ?Category $category = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_OPEN;
        $this->priority = self::PRIORITY_MEDIUM;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get available status choices.
     */
    public static function getStatusChoices(): array
    {
        return [
            'Open' => self::STATUS_OPEN,
            'In Progress' => self::STATUS_IN_PROGRESS,
            'Closed' => self::STATUS_CLOSED,
        ];
    }

    /**
     * Get available priority choices.
     */
    public static function getPriorityChoices(): array
    {
        return [
            'Low' => self::PRIORITY_LOW,
            'Medium' => self::PRIORITY_MEDIUM,
            'High' => self::PRIORITY_HIGH,
        ];
    }
}


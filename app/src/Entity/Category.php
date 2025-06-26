<?php

declare(strict_types=1);

/*
 * This file is part of the Bug Tracker application.
 *
 * (c) 2024 Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category entity for organizing issues.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: 'Category name is required.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Category name must be at least {{ limit }} characters long.',
        maxMessage: 'Category name cannot be longer than {{ limit }} characters.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Description cannot be longer than {{ limit }} characters.'
    )]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Issue::class, orphanRemoval: true)]
    private Collection $issues;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->issues = new ArrayCollection();
    }

    /**
     * Get the category ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the category name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the category name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the category description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the category description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    /**
     * Add an issue to this category.
     */
    public function addIssue(Issue $issue): self
    {
        if (!$this->issues->contains($issue)) {
            $this->issues[] = $issue;
            $issue->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove an issue from this category.
     */
    public function removeIssue(Issue $issue): self
    {
        if ($this->issues->removeElement($issue)) {
            // set the owning side to null (unless already changed)
            if ($issue->getCategory() === $this) {
                $issue->setCategory(null);
            }
        }

        return $this;
    }
}

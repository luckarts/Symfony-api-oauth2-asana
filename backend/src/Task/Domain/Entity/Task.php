<?php

declare(strict_types=1);

namespace App\Task\Domain\Entity;

use App\Task\Domain\Enum\TaskStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: \Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: \App\Project\Domain\Entity\Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private \App\Project\Domain\Entity\Project $project;

    #[ORM\ManyToOne(targetEntity: \App\Project\Domain\Entity\BoardColumn::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?\App\Project\Domain\Entity\BoardColumn $column = null;

    #[ORM\ManyToOne(targetEntity: \App\Project\Domain\Entity\Milestone::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?\App\Project\Domain\Entity\Milestone $milestone = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_task_id', nullable: true, onDelete: 'SET NULL')]
    private ?self $parent = null;

    /** @var Collection<int, Task> */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private Collection $children;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(enumType: TaskStatus::class)]
    private TaskStatus $status = TaskStatus::TODO;

    #[ORM\Column(type: 'boolean')]
    private bool $isCompleted = false;

    #[ORM\Column(type: 'integer')]
    private int $orderIndex = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $title, \App\Project\Domain\Entity\Project $project)
    {
        $this->title = $title;
        $this->project = $project;
        $this->children = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getProject(): \App\Project\Domain\Entity\Project
    {
        return $this->project;
    }

    public function getColumn(): ?\App\Project\Domain\Entity\BoardColumn
    {
        return $this->column;
    }

    public function setColumn(?\App\Project\Domain\Entity\BoardColumn $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function getMilestone(): ?\App\Project\Domain\Entity\Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?\App\Project\Domain\Entity\Milestone $milestone): self
    {
        $this->milestone = $milestone;

        return $this;
    }

    public function getMilestoneId(): ?string
    {
        return $this->milestone?->getId();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /** @return Collection<int, Task> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): self
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    public function getOrderIndex(): int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): self
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid_binary', unique: true)]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'datetime')]
    private $deadline;

    #[ORM\Column(type: 'string', enumType: TaskStatus::class)]
    private $status;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subtasks')]
    private $parentTask;

    #[ORM\OneToMany(mappedBy: 'parentTask', targetEntity: self::class, cascade: ['persist', 'remove'])]
    private $subtasks;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->subtasks = new ArrayCollection();
        $this->status = TaskStatus::PENDING;
    }

    public function getId()
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

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getParentTask(): ?self
    {
        return $this->parentTask;
    }

    public function setParentTask(?self $parentTask): self
    {
        $this->parentTask = $parentTask;
        return $this;
    }

    public function getSubtasks()
    {
        return $this->subtasks;
    }

    public function addSubtask(self $subtask): self
    {
        if (!$this->subtasks->contains($subtask)) {
            $this->subtasks[] = $subtask;
            $subtask->setParentTask($this);
        }
        return $this;
    }

    public function removeSubtask(self $subtask): self
    {
        if ($this->subtasks->removeElement($subtask)) {
            if ($subtask->getParentTask() === $this) {
                $subtask->setParentTask(null);
            }
        }
        return $this;
    }
}
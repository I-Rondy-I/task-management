<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
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

    // Getters and setters
}
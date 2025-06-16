<?php
namespace App\DTO;

use App\Enum\TaskStatus;
use Symfony\Component\Validator\Constraints as Assert;

class TaskDTO
{
    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    public \DateTimeInterface $deadline;

    public ?TaskStatus $status;

    public ?string $parentTaskId = null;
}
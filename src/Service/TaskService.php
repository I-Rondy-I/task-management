<?php
namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\DTO\TaskDTO;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    private $taskRepository;
    private $entityManager;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager)
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    public function createTask(TaskDTO $dto): Task
    {
        $task = new Task();
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);
        $task->setDeadline($dto->deadline);
        $task->setStatus($dto->status);

        if ($dto->parentTaskId) {
            $parentTask = $this->taskRepository->find($dto->parentTaskId);
            $task->setParentTask($parentTask);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function updateTask(Task $task, TaskDTO $dto): Task
    {
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);
        $task->setDeadline($dto->deadline);
        $task->setStatus($dto->status);

        if ($dto->parentTaskId) {
            $parentTask = $this->taskRepository->find($dto->parentTaskId);
            $task->setParentTask($parentTask);
        } else {
            $task->setParentTask(null);
        }

        $this->entityManager->flush();

        return $task;
    }

    public function updateTaskStatus(Task $task, string $status): Task
    {
        $task->setStatus(TaskStatus::from($status));
        $this->entityManager->flush();

        return $task;
    }
}
<?php
namespace App\Service;

use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use App\DTO\TaskDTO;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskService
{
    private $taskRepository;
    private $entityManager;
    private $logger;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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

    public function updateTaskStatus(Task $task, string $status): void
    {
        $validStatuses = ['pending', 'completed', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->logger->error('Invalid status: ' . $status);
            throw new \InvalidArgumentException('Incorrect status: ' . $status);
        }

        $this->logger->info('Updating task status to: ' . $status);
        $task->setStatus(TaskStatus::from($status));
        $this->entityManager->flush();
        $this->logger->info('Task status updated successfully');
    }
}
<?php
namespace App\Tests\Service;

use App\Entity\Task;
use App\Service\TaskService;
use App\DTO\TaskDTO;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    public function testCreateTask(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $service = new TaskService($taskRepository, $entityManager);
        $dto = new TaskDTO();
        $dto->title = 'Test Task';
        $dto->description = 'Description';
        $dto->deadline = new \DateTime();
        $dto->status = TaskStatus::PENDING;

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $task = $service->createTask($dto);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->getTitle());
    }
}
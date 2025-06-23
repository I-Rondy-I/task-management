<?php
namespace App\Controller;

use App\Enum\TaskStatus;
use Psr\Log\LoggerInterface;
use App\DTO\TaskDTO;
use App\Entity\Task;
use App\Service\TaskService;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    private $taskService;
    private $taskRepository;

    public function __construct(TaskService $taskService, TaskRepository $taskRepository)
    {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @OA\Get(
     *     path="/tasks",
     *     summary="Display a list of all tasks",
     *     tags={"Tasks"},
     *     @OA\Response(response=200, description="List of tasks")
     * )
     */
    #[Route('', name: 'task_index', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->taskRepository->findAll();
        return $this->render('task/index.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get a list of all tasks",
     *     tags={"Tasks"},
     *     @OA\Response(
     *         response=200,
     *         description="Task list",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     )
     * )
     */
    #[Route('/api', name: 'api_task_index', methods: ['GET'])]
    public function apiIndex(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        return $this->json($tasks, 200, [], ['groups' => 'task']);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create new task",
     *     tags={"Tasks"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/TaskDTO")
     *     ),
     *     @OA\Response(response=201, description="Task created")
     * )
     */
    #[Route('/api', name: 'api_task_create', methods: ['POST'])]
    public function apiCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new TaskDTO();
        $dto->title = $data['title'] ?? '';
        $dto->description = $data['description'] ?? '';
        $dto->deadline = new \DateTime($data['deadline'] ?? 'now');
        $dto->status = TaskStatus::from($data['status'] ?? 'pending');
        $dto->parentTaskId = $data['parentTaskId'] ?? null;

        $task = $this->taskService->createTask($dto);
        return $this->json($task, 201, [], ['groups' => 'task']);
    }

    /**
     * @OA\Get(
     *     path="/tasks/create",
     *     summary="Display the task creation form",
     *     tags={"Tasks"},
     *     @OA\Response(response=200, description="Task creation form")
     * )
     */
    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $dto = new TaskDTO();
        $form = $this->createForm(TaskType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->createTask($dto);
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get task details",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Task details",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    #[Route('/api/{id}', name: 'api_task_show', methods: ['GET'])]
    public function apiShow(Task $task): JsonResponse
    {
        return $this->json($task, 200, [], ['groups' => 'task']);
    }

    #[Route('/kanban', name: 'task_kanban', methods: ['GET'])]
    public function kanban(): Response
    {
        $pending = $this->taskRepository->findByStatus(TaskStatus::PENDING->value);
        $completed = $this->taskRepository->findByStatus(TaskStatus::COMPLETED->value);
        $rejected = $this->taskRepository->findByStatus(TaskStatus::REJECTED->value);

        return $this->render('task/kanban.html.twig', [
            'pending' => $pending,
            'completed' => $completed,
            'rejected' => $rejected,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}",
     *     summary="Show task details",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Task details")
     * )
     */
    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        return $this->render('task/show.html.twig', ['task' => $task]);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update task",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/TaskDTO")
     *     ),
     *     @OA\Response(response=200, description="Task updated")
     * )
     */
    #[Route('/api/{id}', name: 'api_task_edit', methods: ['PUT'])]
    public function apiEdit(Request $request, Task $task): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new TaskDTO();
        $dto->title = $data['title'] ?? $task->getTitle();
        $dto->description = $data['description'] ?? $task->getDescription();
        $dto->deadline = new \DateTime($data['deadline'] ?? $task->getDeadline()->format('Y-m-d H:i:s'));
        $dto->status = TaskStatus::from($data['status'] ?? $task->getStatus()->value);
        $dto->parentTaskId = $data['parentTaskId'] ?? null;

        $task = $this->taskService->updateTask($task, $dto);
        return $this->json($task, 200, [], ['groups' => 'task']);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}/edit",
     *     summary="Display the task editing form",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Task editing form")
     * )
     */
    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        $dto = new TaskDTO();
        $dto->title = $task->getTitle();
        $dto->description = $task->getDescription();
        $dto->deadline = $task->getDeadline();
        $dto->status = $task->getStatus();
        $dto->parentTaskId = $task->getParentTask() ? $task->getParentTask()->getId()->toString() : null;

        $form = $this->createForm(TaskType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->updateTask($task, $dto);
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', ['form' => $form->createView(), 'task' => $task]);
    }

    #[Route('/{id}/status', name: 'task_update_status', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    #[OA\Post(
        path: '/api/tasks/{id}/status',
        summary: 'Update task status',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'completed', 'rejected'])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status updated'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 400, description: 'Incorrect status')
        ]
    )]
    public function updateStatus(Request $request, Task $task = null, LoggerInterface $logger): JsonResponse
    {
        if (!$task) {
            $logger->error('Task not found for ID: ' . $request->attributes->get('id'));
            return new JsonResponse(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        try {
            $status = $request->request->get('status');
            $logger->info('Updating status for task ID: ' . $task->getId()->toString() . ' to: ' . $status);
            if (!in_array($status, ['pending', 'completed', 'rejected'])) {
                $logger->error('Invalid status provided: ' . $status);
                return new JsonResponse(['status' => 'error', 'message' => 'Incorrect status'], 400);
            }
            $this->taskService->updateTaskStatus($task, $status);
            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $logger->error('Error updating task status: ' . $e->getMessage());
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while updating the status'], 500);
        }
    }
}
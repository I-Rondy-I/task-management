<?php
namespace App\Controller;

use App\Entity\Task;
use App\Service\TaskService;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *     path="/api/tasks",
     *     summary="Получить список всех задач",
     *     tags={"Tasks"},
     *     @OA\Response(response=200, description="Список задач")
     * )
     */
    #[Route('', name: 'task_index', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->taskRepository->findAll();
        return $this->render('task/index.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Создать новую задачу",
     *     tags={"Tasks"},
     *     @OA\Response(response=201, description="Задача создана")
     * )
     */
    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $form = $this->createForm(TaskType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $this->taskService->createTask($dto);
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Получить детали задачи",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Детали задачи")
     * )
     */
    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', ['task' => $task]);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Обновить задачу",
     *     tags={"Tasks"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Задача обновлена")
     * )
     */
    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $this->taskService->updateTask($task, $dto);
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', ['form' => $form->createView(), 'task' => $task]);
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

    #[Route('/{id}/status', name: 'task_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $status = $request->request->get('status');
        $this->taskService->updateTaskStatus($task, $status);
        return new JsonResponse(['status' => 'success']);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    
private $taskService;

/**
 * Create a new TaskController.
 * 
* @param TaskService $taskService
*/
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request) : JsonResponse
    {
        {
        /** @var User $user */
        $user = $request->user();

        $task = $this->taskService->createTask($user,$request->validated());

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task,
        ], 201);
    }
    }

    /**
     * Display a listing of the tasks for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewTasks(Request $request) : JsonResponse
    {
        $user = $request->user();
        $tasks = $this->taskService->listTasks($user);

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Update the specified task in storage.
     *
     * @param  \Illuminate\Http\UpdateTaskRequest  $request
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, int $taskId) : JsonResponse
    {
        $user = $request->user();
        $task = $this->taskService->updateTask($user, $taskId, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully.',
            'task' => $task,
        ]);
    }
}

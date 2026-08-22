<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
;

class TaskService
{
    /**
     * Create a new task for a user.
     *
     * @param User $user
     * @param array $data
     * @return Task
     */
    public function createTask(User $user, array $data): Task
    {
        return $user->tasks()->create($data);
    }

    /**
     * List all tasks for a user.
     * if the user is an owner, return all tasks.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listTasks(User $user)
    {
        if ($user->isOwner()) {
            return Task::all();
        }
        return $user->tasks()->get();
    }

    /**
     * Update a task for a user.
     *
     * @param User $user
     * @param int $taskId
     * @param array $data
     * @return Task
     */
    public function updateTask(User $user, int  $taskId, array $data): Task
        {
            // Find the task among the tasks that belong to the user.
        $task = $user->tasks()->find($taskId);

        // Check if the task exists.
        if (!$task) {
            throw new \Exception('Task not found.');
        }

        // Update the task.
        $task->update($data);

        return $task;

        }


        /**
         * Delete a task for a user.
         *
         * @param User $user
         * @param int $taskId
         * @return bool
         */
        public function deleteTask(User $user, int $taskId): bool
        {
            // Find the task among the tasks that belong to the user.   
            $task = $user->tasks()->find($taskId);

            if (!$task) {
                throw new \Exception('Task not found.');
            }

            return $task->delete();
        }

        /**
         * Get a specific task for a user.
         *
         * @param User $user
         * @param int $taskId
         * @return Task|null
         */
        public function getSingleTask(User $user, int $taskId): Task
        {
            $task = $user->tasks()->find($taskId);

            if (!$task) {
                throw new \Exception('Task not found.');
            }

            return $task;
        }
}        

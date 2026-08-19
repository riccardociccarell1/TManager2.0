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
}

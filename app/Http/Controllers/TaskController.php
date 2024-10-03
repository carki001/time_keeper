<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function searchForNewTask($user_id, $task_name_search)
    {
        $user = User::find($user_id);
        $tasks = $user->tasks()->where('name', 'LIKE', '%' . $task_name_search . '%')->get();

        return ['tasks' => $tasks];
    }

    public function setProjectSuggestion($user_id, $task_name)
    {
        $user = User::find($user_id);
        $tasksCount = $user->tasks()->where('name', $task_name)->count();

        if ($tasksCount > 1 || $tasksCount < 1) {
            $suggestedProjectId = NULL;
            $taskHasOneProject = FALSE;
        } else {
            $suggestedProjectId = $user->tasks()->where('name', $task_name)->value('project_id');
            $taskHasOneProject = TRUE;
        }

        return ['taskHasOneProject' => $taskHasOneProject, 'suggestedProjectId' => $suggestedProjectId];
    }
}

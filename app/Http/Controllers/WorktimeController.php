<?php

namespace App\Http\Controllers;

use App\Models\Worktime;
use App\Models\User;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Team;
use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Session;

class WorktimeController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        $page = $request->page;
        $items_per_page = $request->items_per_page;
        $search = $request->search;

        $user = User::find($user_id);
        $currentTimezone = $user->preferred_timezone;
        $timezones = config('constants.TIMEZONES');

        $teams = $user->teams()->get();

        if (!$teams->isEmpty()) {
            $projects = Project::whereBelongsTo($teams, 'team')->get();
        } else {
            $projects = collect([]);
        }


        $filteredProjects = $projects->filter(function ($item) use ($search) {
            return false !== stripos($item->name, $search);
        });

        if ($filteredProjects->isEmpty()) {
            $tasksQuery = $user->tasks()
                ->where(function ($query) use ($search) {
                    if (isset($search) && !empty($search)) {
                        $query->where('name', 'LIKE', '%' . $search . '%');
                    }
                });
        } else {
            $tasksQuery = $user->tasks()->whereBelongsTo($filteredProjects, 'project')
                ->orWhere(function ($query) use ($search) {
                    if (isset($search) && !empty($search)) {
                        $query->where('name', 'LIKE', '%' . $search . '%');
                    }
                });
        }
        $tasksCount = $tasksQuery->count();
        $tasks = $tasksQuery->offset(($page - 1) * $items_per_page)->limit($items_per_page)->orderBy('last_worktime_date', 'desc')->get();

        $runningTaskPosition = NULL;

        foreach ($tasks as $taskIndex => $task) {
            $ongoingWorktimeCheck = $task->worktimes()->whereNull('finished_at')->first();
            $taskCarbonInstance = new Carbon($task->last_worktime_date, "UTC");
            $task->formattedDate = $taskCarbonInstance->timezone($currentTimezone)->format($this->dateFormatByLanguage($user->preferred_language, "date_seconds_format"));
            if ($ongoingWorktimeCheck) {
                $task->isRunning = TRUE;
                $runningTaskPosition = $taskIndex + 1;
            } else {
                $task->isRunning = FALSE;
            }

            $taskWorktimesQuery = $task->worktimes()->whereNotNull('finished_at');
            $taskWorktimes = $taskWorktimesQuery->get();
            $task->worktimes = $taskWorktimes;
            $task->worktimes_count = $taskWorktimesQuery->count();
            $taskDuration = 0;

            foreach ($taskWorktimes as $twt) {
                $worktimeDuration = Carbon::parse($twt->finished_at)->diffInSeconds(Carbon::parse($twt->started_at));
                $taskDuration += $worktimeDuration;

                $twt->duration = $worktimeDuration;
                $twt->hours = floor($worktimeDuration / 60 / 60);
                $worktimeDurationInHours = $worktimeDuration / 60 / 60;
                $twt->minutes = floor(60 * ($worktimeDurationInHours - $twt->hours));
                $remainingWorktimeMinutes = 60 * ($worktimeDurationInHours - $twt->hours);
                $twt->seconds = round(60 * ($remainingWorktimeMinutes - $twt->minutes));
                $twt->formattedDuration = ($twt->hours < 10 ? "0" . $twt->hours : $twt->hours) .
                    ":" . ($twt->minutes < 10 ? "0" . $twt->minutes : $twt->minutes) .
                    ":" . ($twt->seconds < 10 ? "0" . $twt->seconds : $twt->seconds);

                $carbonInstance = new Carbon($twt->started_at, "UTC");
                $twt->started_at_formatted = $carbonInstance->timezone($currentTimezone)->format($this->dateFormatByLanguage($user->preferred_language, "date_seconds_format"));
                $twt->started_at_short_formatted = $carbonInstance->timezone($currentTimezone)->format($this->dateFormatByLanguage($user->preferred_language, "date_short_format"));
                $twt->started_at_short_standard = $carbonInstance->timezone($currentTimezone)->format("Y-m-d");
                $twt->task_name = $task->name;
                $twt->task_name_temporal = "";
                $twt->project_id = $task->project_id;
                $twt->project_id_temporal = 0;
                $twt->is_editing = FALSE;
                $twt->is_duration_picker_visible = FALSE;
                $twt->duration_temporal = NULL;
                $twt->is_started_date_picker_visible = FALSE;
                $twt->started_short_standard_temporal = "";
                $twt->started_short_formatted_temporal = "";
                $twt->is_started_at_time_picker_visible = FALSE;
                $twt->started_at_time_formatted = explode(" ", $twt->started_at_formatted)[1];
                $twt->started_at_time_temporal = NULL;
            }
            $timeInformation = new \stdClass();
            $timeInformation->duration = $taskDuration;
            $timeInformation->hours = floor($taskDuration / 60 / 60);
            $taskDurationInHours = $taskDuration / 60 / 60;
            $timeInformation->minutes = floor(60 * ($taskDurationInHours - $timeInformation->hours));
            $remainingMinutes = 60 * ($taskDurationInHours - $timeInformation->hours);
            $timeInformation->seconds = round(60 * ($remainingMinutes - $timeInformation->minutes));
            $timeInformation->formattedDuration = ($timeInformation->hours < 10 ? "0" . $timeInformation->hours : $timeInformation->hours) .
                ":" . ($timeInformation->minutes < 10 ? "0" . $timeInformation->minutes : $timeInformation->minutes) .
                ":" . ($timeInformation->seconds < 10 ? "0" . $timeInformation->seconds : $timeInformation->seconds);
            $task->timeInformation = $timeInformation;

            $task->project_name = $task->project()->value('name');
        }

        $ongoingWorktime = Worktime::where('user_id', $user_id)->whereNull('finished_at')->first();

        if ($ongoingWorktime) {

            $ongoingWorktime_task = $ongoingWorktime->task()->first();
            $ongoingWorktime->task = $ongoingWorktime_task;
            $ongoingWorktime_project = $ongoingWorktime_task->project()->first();
            $ongoingWorktime->project = $ongoingWorktime_project;
            $ongoingWorktime->runningTaskPosition = isset($runningTaskPosition) ? $runningTaskPosition : NULL;
        }

        if ($tasksCount == 0) {
            $dummyTask = new \stdClass();
            $dummyTask->name = "-";
            $dummyTask->project_name = "-";
            $dummyTask->worktimes_count = "-";
            $dummyTask->formattedDate = "-";
            $dummyTimeInformation = new \stdClass();
            $dummyTimeInformation->duration = "-";
            $dummyTimeInformation->formattedDuration = "-";
            $dummyTask->timeInformation = $dummyTimeInformation;
            $dummyTask->withoutTasks = TRUE;
            $tasks[] = $dummyTask;
            $tasksCount = 1;

            $withoutTasks = TRUE;
        } else {
            $withoutTasks = FALSE;
        }

        return [
            'tasks' => $tasks,
            'projects' => $projects,
            'ongoingWorktime' => $ongoingWorktime,
            'timezones' => $timezones,
            'currentTimezone' => $currentTimezone,
            'tasksCount' => $tasksCount,
            'withoutTasks' => $withoutTasks,

        ];
    }

    public function createWorktime(Request $request)
    {
        $info = $request->all();

        $existingTask = Task::where('name', trim($info['name']))->where('project_id', $info['project_id'])->where('user_id', $info['user_id'])->first();

        if (isset($existingTask)) {
            $task = $existingTask;
            $task->last_worktime_date = $info['started_at'];
            $task->save();
            $task->refresh();
        } else {
            $task = new Task();
            $task->name = trim($info['name']);
            $task->is_active = $info['is_active'] == 1 ? 1 : 0;
            $task->project_id = $info['project_id'];
            $task->user_id = $info['user_id'];
            $task->last_worktime_date = $info['started_at'];
            $task->save();
            $task->refresh();
        }

        $worktime = new Worktime();
        $worktime->user_id = $info['user_id'];
        $worktime->task_id = $task->id;
        $worktime->started_at = $info['started_at'];
        $worktime->save();
        $worktime->refresh();
        $worktime->task_name = $task->name;
        $worktime->task_name_temporal = "";
        $worktime->project_id = $task->project_id;
        $worktime->project_id_temporal = 0;
        $worktime->is_editing = FALSE;
        $worktime->is_duration_picker_visible = FALSE;
        $worktime->duration_temporal = NULL;
        $worktime->is_started_date_picker_visible = FALSE;
        $worktime->started_short_standard_temporal = "";
        $worktime->started_short_formatted_temporal = "";
        $worktime->is_started_at_time_picker_visible = FALSE;

        return ['newTaskId' => $task->id, 'task' => $task, 'worktime' => $worktime];
    }

    public function updateWorktime(Request $request)
    {
        $info = $request->all();
        $worktime = Worktime::find($info['id']);
        $worktime->finished_at = $info['finished_at'];
        $worktime->save();

        $task = $worktime->task()->first();

        $taskModified = $this->formatTask($task);

        return $taskModified;
    }

    public function deleteTask($task_id)
    {
        $task = Task::find($task_id);
        $task->delete();
        return 'success';
    }

    public function getOngoingWorktime($user_id)
    {
        $ongoingWorktime = Worktime::where('user_id', $user_id)->whereNull('finished_at')->first();

        if ($ongoingWorktime) {

            $ongoingWorktime_task = $ongoingWorktime->task()->first();
            $ongoingWorktime->task = $ongoingWorktime_task;
            $ongoingWorktime_project = $ongoingWorktime_task->project()->first();
            $ongoingWorktime->project = $ongoingWorktime_project;
            $ongoingWorktime->runningTaskPosition = NULL;
        }

        return $ongoingWorktime;
    }

    public function saveWorktime(Request $request)
    {
        $worktime = Worktime::find($request->id);
        $user = $worktime->user()->first();
        $preferredTimezone = $user->preferred_timezone;
        $started_at_locale = $request->started_at_short_standard . " " . $request->started_at_time_formatted;
        $carbon = new Carbon($started_at_locale, $preferredTimezone);

        $worktime->started_at = $carbon->timezone("UTC")->format("Y-m-d H:i:s");

        $worktimeDuration = explode(":", $request->formattedDuration);
        $finished_at = Carbon::parse($worktime->started_at)->addHours(intval($worktimeDuration[0]))
            ->addMinutes(intval($worktimeDuration[1]))
            ->addSeconds(intval($worktimeDuration[2]))->timezone("UTC")->format("Y-m-d H:i:s");

        $worktime->finished_at = $finished_at;
        $previousTaskId = $worktime->task_id;

        $isNewTask = FALSE;
        $existingTask = Task::where("name", $request->task_name)->where("project_id", $request->project_id)->first();
        if ($existingTask) {
            $worktime->task_id = $existingTask->id;
            $existingTask->is_active = 1;
            $existingTask->save();
            $existingTask->refresh();
            $task = $existingTask;
        } else {
            $newTask = new Task();
            $newTask->name = $request->task_name;
            $newTask->is_active = 1;
            $newTask->project_id = $request->project_id;
            $newTask->user_id = $user->id;
            $newTask->last_worktime_date = $worktime->started_at;
            $newTask->save();
            $newTask->refresh();
            $task = $newTask;
            $isNewTask = TRUE;
        }

        $worktime->task_id = $task->id;
        $worktime->save();
        $worktime->refresh();

        if ($existingTask) {
            $task->last_worktime_date =  $task->worktimes()->latest('started_at')->value('started_at');
            $task->save();
            $task->refresh();
        }

        $isPreviousTaskDeleted = FALSE;
        $previousTask = Task::find($previousTaskId);
        $previousTaskWorktimesCount = $previousTask->worktimes()->count();
        if ($previousTaskWorktimesCount < 1) {
            $previousTask->delete();
            $isPreviousTaskDeleted = TRUE;
            $previousTaskModified = NULL;
        } else {
            $previousTask->last_worktime_date =  $previousTask->worktimes()->latest('started_at')->value('started_at');
            $previousTask->save();
            $previousTask->refresh();
            $previousTaskModified = $this->formatTask($previousTask);
        }

        $isSameTask = $previousTaskId == $task->id ? TRUE : FALSE;

        $taskModified = $this->formatTask($task);
        $worktimeModified = $taskModified->worktimes->where('id', $worktime->id)->first();

        return [
            'isSameTask' => $isSameTask,
            'isNewTask' => $isNewTask,
            'previousTaskId' => $previousTaskId,
            'previousTask' => $previousTaskModified,
            'taskId' => $task->id,
            'isPreviousTaskDeleted' => $isPreviousTaskDeleted,
            'task' => $taskModified,
            'worktime' => $worktimeModified
        ];
    }

    public function formatTask($task)
    {
        $currentTimezone = $task->user()->value('preferred_timezone');
        $preferredLanguage = $task->user()->value('preferred_language');

        $task->isRunning = FALSE;
        $taskWorktimesQuery = $task->worktimes()->whereNotNull('finished_at');
        $taskWorktimes = $taskWorktimesQuery->get();
        $task->worktimes = $taskWorktimes;
        $task->worktimes_count = $taskWorktimesQuery->count();
        $taskDuration = 0;
        foreach ($taskWorktimes as $twt) {
            $worktimeDuration = Carbon::parse($twt->finished_at)->diffInSeconds(Carbon::parse($twt->started_at));
            $taskDuration += $worktimeDuration;

            $twt->duration = $worktimeDuration;
            $twt->hours = floor($worktimeDuration / 60 / 60);
            $worktimeDurationInHours = $worktimeDuration / 60 / 60;
            $twt->minutes = floor(60 * ($worktimeDurationInHours - $twt->hours));
            $remainingWorktimeMinutes = 60 * ($worktimeDurationInHours - $twt->hours);
            $twt->seconds = round(60 * ($remainingWorktimeMinutes - $twt->minutes));
            $twt->formattedDuration = ($twt->hours < 10 ? "0" . $twt->hours : $twt->hours) .
                ":" . ($twt->minutes < 10 ? "0" . $twt->minutes : $twt->minutes) .
                ":" . ($twt->seconds < 10 ? "0" . $twt->seconds : $twt->seconds);

            $carbon = new Carbon($twt->started_at, "UTC");
            $twt->started_at_formatted = $carbon->timezone($currentTimezone)->format($this->dateFormatByLanguage($preferredLanguage, "date_seconds_format"));
            $twt->started_at_short_formatted = $carbon->timezone($currentTimezone)->format($this->dateFormatByLanguage($preferredLanguage, "date_short_format"));
            $twt->task_name = $task->name;
            $twt->task_name_temporal = "";
            $twt->project_id = $task->project_id;
            $twt->project_id_temporal = 0;
            $twt->is_editing = FALSE;
            $twt->is_duration_picker_visible = FALSE;
            $twt->duration_temporal = NULL;
            $twt->is_started_date_picker_visible = FALSE;
            $twt->started_short_standard_temporal = "";
            $twt->started_short_formatted_temporal = "";
            $twt->is_started_at_time_picker_visible = FALSE;
            $twt->started_at_time_formatted = explode(" ", $twt->started_at_formatted)[1];
            $twt->started_at_time_temporal = NULL;
        }
        $timeInformation = new \stdClass();
        $timeInformation->duration = $taskDuration;
        $timeInformation->hours = floor($taskDuration / 60 / 60);
        $taskDurationInHours = $taskDuration / 60 / 60;
        $timeInformation->minutes = floor(60 * ($taskDurationInHours - $timeInformation->hours));
        $remainingMinutes = 60 * ($taskDurationInHours - $timeInformation->hours);
        $timeInformation->seconds = round(60 * ($remainingMinutes - $timeInformation->minutes));
        $timeInformation->formattedDuration = ($timeInformation->hours < 10 ? "0" . $timeInformation->hours : $timeInformation->hours) .
            ":" . ($timeInformation->minutes < 10 ? "0" . $timeInformation->minutes : $timeInformation->minutes) .
            ":" . ($timeInformation->seconds < 10 ? "0" . $timeInformation->seconds : $timeInformation->seconds);
        $task->timeInformation = $timeInformation;

        $taskCarbonInstance = new Carbon($task->last_worktime_date, "UTC");
        $task->formattedDate = $taskCarbonInstance->timezone($currentTimezone)->format($this->dateFormatByLanguage($preferredLanguage, "date_seconds_format"));

        $task->project_name = $task->project()->value('name');

        return $task;
    }

    public function formatWorktime($worktime, $task)
    {
        $currentTimezone = $task->user()->value('preferred_timezone');
        $preferredLanguage = $task->user()->value('preferred_language');

        $worktimeDuration = Carbon::parse($worktime->finished_at)->diffInSeconds(Carbon::parse($worktime->started_at));

        $worktime->duration = $worktimeDuration;
        $worktime->hours = floor($worktimeDuration / 60 / 60);
        $worktimeDurationInHours = $worktimeDuration / 60 / 60;
        $worktime->minutes = floor(60 * ($worktimeDurationInHours - $worktime->hours));
        $remainingWorktimeMinutes = 60 * ($worktimeDurationInHours - $worktime->hours);
        $worktime->seconds = round(60 * ($remainingWorktimeMinutes - $worktime->minutes));
        $worktime->formattedDuration = ($worktime->hours < 10 ? "0" . $worktime->hours : $worktime->hours) .
            ":" . ($worktime->minutes < 10 ? "0" . $worktime->minutes : $worktime->minutes) .
            ":" . ($worktime->seconds < 10 ? "0" . $worktime->seconds : $worktime->seconds);

        $carbon = new Carbon($worktime->started_at, "UTC");
        $worktime->started_at_formatted = $carbon->timezone($currentTimezone)->format($this->dateFormatByLanguage($preferredLanguage, "date_seconds_format"));
        $worktime->started_at_short_formatted = $carbon->timezone($currentTimezone)->format($this->dateFormatByLanguage($preferredLanguage, "date_short_format"));
        $worktime->task_name = $task->name;
        $worktime->task_name_temporal = "";
        $worktime->project_id = $task->project_id;
        $worktime->project_id_temporal = 0;
        $worktime->is_editing = FALSE;
        $worktime->is_duration_picker_visible = FALSE;
        $worktime->duration_temporal = NULL;
        $worktime->is_started_date_picker_visible = FALSE;
        $worktime->started_short_standard_temporal = "";
        $worktime->started_short_formatted_temporal = "";
        $worktime->is_started_at_time_picker_visible = FALSE;
        $worktime->started_at_time_formatted = explode(" ", $worktime->started_at_formatted)[1];
        $worktime->started_at_time_temporal = NULL;

        return $worktime;
    }

    public function deleteWorktime($id)
    {
        $worktime = Worktime::find($id);
        $task = $worktime->task()->first();

        $worktime->delete();

        $worktimeCount = $task->worktimes()->count();
        $isTaskDeleted = FALSE;
        if ($worktimeCount < 1) {
            $task->delete();
            $isTaskDeleted = TRUE;
            $taskModified = NULL;
        } else {
            $task->last_worktime_date =  $task->worktimes()->latest('started_at')->value('started_at');
            $taskModified = $this->formatTask($task);
        }

        return ['isTaskDeleted' => $isTaskDeleted, 'task' => $taskModified];
    }
}

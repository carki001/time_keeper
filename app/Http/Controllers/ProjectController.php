<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index($user_id)
    {

        $currentUser = User::find($user_id);
        $currentUserTeams = $currentUser->teams()->get();

        if ($currentUser->role === "admin") {
            $projects = Project::get();
        } else {
            $projects = Project::whereBelongsTo($currentUserTeams, 'team')->get();
        }

        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');

        foreach ($projects as $pjt) {
            $team = Team::where('id', $pjt->team_id)->first();
            $pjt->team_name = $team ? $team->name : "";

            $carbonInstance = new Carbon($pjt->created_at, $appTimezone);
            $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
            $pjt->formattedDatetime = $formattedDatetime;
        }

        $teams = Team::get();
        return ['projects' => $projects, 'teams' => $teams,];
    }

    public function store(Request $request)
    {
        $project = new Project();
        $project->name = $request->name;
        $project->is_active = $request->is_active  == 1 ? 1 : 0;
        $project->team_id = $request->team_id;
        $project->save();
        $project->refresh();

        $team = Team::where('id', $project->team_id)->first();
        $project->team_name = $team ? $team->name : "";

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($project->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $project->formattedDatetime = $formattedDatetime;

        return response()->json($project, 200);
    }

    public function update($id, Request $request)
    {
        $project = Project::findOrFail($id);
        $project->name = $request->name;
        $project->is_active = $request->is_active  == 1 ? 1 : 0;
        $project->team_id = $request->team_id;
        $project->save();
        $project->refresh();

        $team = Team::where('id', $project->team_id)->first();
        $project->team_name = $team ? $team->name : "";

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($project->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $project->formattedDatetime = $formattedDatetime;

        return response()->json($project, 200);
    }


    public function delete($id, Request $request)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return 204;
    }

    public function changeActivationStatus($id, $new_is_active)
    {
        $project = Project::findOrFail($id);
        $project->is_active = $new_is_active;
        $project->save();

        return "success";
    }
}

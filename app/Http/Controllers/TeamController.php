<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index($user_id)
    {
        $currentUser = User::find($user_id);
        $users = User::get();
        if ($currentUser->role === "admin") {
            $teams = Team::get();
        } else {
            $teams = $currentUser->teams()->get();
        }


        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');

        foreach ($teams as $team) {

            $team->team = $team->users()->get();
            $first_leader = $team->users()->where('work_role', 'leader')->first();
            $team->first_leader = $first_leader ? $first_leader->name : "";

            $carbonInstance = new Carbon($team->created_at, $appTimezone);
            $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
            $team->formattedDatetime = $formattedDatetime;
        }

        $workRoles =   config('constants.WORK_ROLES');

        return ['teams' => $teams, 'users' => $users, 'workRoles' => $workRoles];
    }


    public function store(Request $request)
    {
        $team = new Team();
        $team->name = $request->name;
        $team->save();
        $newAssignedUsers = [];

        foreach ($request->team as $assignedUser) {
            $newAssignedUsers[$assignedUser['pivot']['user_id']] = [
                'is_user_active' => $assignedUser['pivot']['is_user_active'] == 1 ? 1 : 0,
                'work_role' => $assignedUser['pivot']['work_role']
            ];
        }
        $team->users()->sync($newAssignedUsers);

        $team->refresh();
        $team->team = $team->users()->get();
        $first_leader = $team->users()->where('work_role', 'leader')->first();
        $team->first_leader = $first_leader ? $first_leader->name : "";

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($team->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $team->formattedDatetime = $formattedDatetime;


        return response()->json($team, 200);
    }

    public function update($id, Request $request)
    {
        $team = Team::findOrFail($id);
        $team->name = $request->name;
        $team->save();
        $newAssignedUsers = [];

        foreach ($request->team as $assignedUser) {
            $newAssignedUsers[$assignedUser['pivot']['user_id']] = [
                'is_user_active' => $assignedUser['pivot']['is_user_active'] == 1 ? 1 : 0,
                'work_role' => $assignedUser['pivot']['work_role']
            ];
        }
        $team->users()->sync($newAssignedUsers);

        $team->refresh();
        $team->team = $team->users()->get();
        $first_leader = $team->users()->where('work_role', 'leader')->first();
        $team->first_leader = $first_leader ? $first_leader->name : "";

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($team->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $team->formattedDatetime = $formattedDatetime;

        return response()->json($team, 200);
    }

    public function delete($id, Request $request)
    {
        $team = Team::findOrFail($id);
        $team->delete();

        return 204;
    }
}

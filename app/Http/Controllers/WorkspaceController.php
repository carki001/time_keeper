<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{

    public function index()
    {
        $workspaces = Workspace::get();
        $users = User::get();
        foreach ($workspaces as $ws) {

            $ws->team = $ws->users()->get();
            $first_leader = $ws->users()->where('work_role', 'leader')->first();
            $ws->first_leader = $first_leader ? $first_leader->name : "";
        }

        $workRoles =   config('constants.WORK_ROLES');

        return ['workspaces' => $workspaces, 'users' => $users, 'workRoles' => $workRoles];
    }


    public function store(Request $request)
    {
        $workspace = new Workspace();
        $workspace->name = $request->name;
        $workspace->save();
        $newAssignedUsers = [];

        foreach ($request->team as $assignedUser) {
            $newAssignedUsers[$assignedUser['pivot']['user_id']] = [
                'is_user_active' => $assignedUser['pivot']['is_user_active'] == 1 ? 1 : 0,
                'work_role' => $assignedUser['pivot']['work_role']
            ];
        }
        $workspace->users()->sync($newAssignedUsers);

        $workspace->refresh();
        $workspace->team = $workspace->users()->get();
        $first_leader = $workspace->users()->where('work_role', 'leader')->first();
        $workspace->first_leader = $first_leader ? $first_leader->name : "";


        return response()->json($workspace, 200);
    }

    public function update($id, Request $request)
    {
        $workspace = Workspace::findOrFail($id);
        $workspace->name = $request->name;
        $workspace->save();
        $newAssignedUsers = [];

        foreach ($request->team as $assignedUser) {
            $newAssignedUsers[$assignedUser['pivot']['user_id']] = [
                'is_user_active' => $assignedUser['pivot']['is_user_active'] == 1 ? 1 : 0,
                'work_role' => $assignedUser['pivot']['work_role']
            ];
        }
        $workspace->users()->sync($newAssignedUsers);

        $workspace->refresh();
        $workspace->team = $workspace->users()->get();
        $first_leader = $workspace->users()->where('work_role', 'leader')->first();
        $workspace->first_leader = $first_leader ? $first_leader->name : "";

        return response()->json($workspace, 200);
    }

    public function delete($id, Request $request)
    {
        $workspace = Workspace::findOrFail($id);
        $workspace->delete();

        return 204;
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorktimeController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout',  [AuthController::class, 'logout']);
        Route::get('profile',  [AuthController::class, 'profile']);
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['middleware' => 'scope:admin,user'], function () {
        Route::get('teamList/{user_id}',  [TeamController::class, 'index']);
        Route::put('updateTeam/{id}', [TeamController::class, 'update']);
        Route::post('storeTeam',  [TeamController::class, 'store']);
        Route::delete('deleteTeam/{id}',  [TeamController::class, 'delete']);

        Route::get('projectList/{user_id}',  [ProjectController::class, 'index']);
        Route::put('updateProject/{id}', [ProjectController::class, 'update']);
        Route::post('storeProject',  [ProjectController::class, 'store']);
        Route::delete('deleteProject/{id}',  [ProjectController::class, 'delete']);
        Route::get('changeProjectActivationStatus/{id}/{new_is_active}',  [ProjectController::class, 'changeActivationStatus']);

        Route::post('worktimeList',  [WorktimeController::class, 'index']);
        Route::put('createWorktime', [WorktimeController::class, 'createWorktime']);
        Route::put('updateWorktime',  [WorktimeController::class, 'updateWorktime']);
        Route::delete('deleteTask/{id}',  [WorktimeController::class, 'deleteTask']);
        Route::get('getOngoingWorktime/{user_id}',  [WorktimeController::class, 'getOngoingWorktime']);
        Route::get('searchForNewTask/{user_id}/{task_name_search}',  [TaskController::class, 'searchForNewTask']);
        Route::post('saveWorktime',  [WorktimeController::class, 'saveWorktime']);
        Route::delete('deleteWorktime/{id}',  [WorktimeController::class, 'deleteWorktime']);
        Route::get('setProjectSuggestion/{user_id}/{task_name}',  [TaskController::class, 'setProjectSuggestion']);

        Route::post('updateOwnUser', [UserController::class, 'updateOwnUser']);
        Route::get('getTimezones', [SettingController::class, 'getTimezones']);
    });

    Route::group(['middleware' => 'scope:admin'], function () {
        Route::get('userList/{user_id}',  [UserController::class, 'index'])->name('users');
        Route::post('storeUser',  [UserController::class, 'store']);
        Route::put('updateUser/{id}', [UserController::class, 'update']);
        Route::delete('deleteUser/{id}',  [UserController::class, 'delete']);
        Route::get('userRoles', [UserController::class, 'getUserRoles']);



        Route::get('settings', [SettingController::class, 'index']);
        Route::put('updateSetting', [SettingController::class, 'update']);
    });
});

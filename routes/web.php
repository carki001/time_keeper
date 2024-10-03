<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WorktimeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('getLanguages', [SettingController::class, 'getLanguages']);

// Backend
Route::get('/login', [BackendController::class, 'index']);
Route::get('/admin', [BackendController::class, 'index']);
Route::get('admin/users', [BackendController::class, 'index']);
Route::get('admin/settings', [BackendController::class, 'index']);
Route::get('admin/teams', [BackendController::class, 'index']);
Route::get('admin/projects', [BackendController::class, 'index']);
Route::get('admin/worktimes', [BackendController::class, 'index']);

Route::get('404', [BackendController::class, 'index']);

Route::get('correction', [WorktimeController::class, 'correction']);

Route::get('/mod-first-user', [UserController::class, 'modUser']);

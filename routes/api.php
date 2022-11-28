<?php

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;

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

//unauthenticated routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//authenticated routes




Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/files/{file}', function (File $file) {
        return response()->file(storage_path('app\public\files\\' . $file->path));
    })->middleware('can:view,file');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/owned-groups', [GroupController::class, 'ownedGroups']);
    Route::get('/group/{id}', [GroupController::class, 'show']);
    //transactional routes 
    Route::middleware('transactional')->group(function () {
        //TODO put here each route that updates,deletes,inserts anything
        Route::post('/file', [FileController::class, 'store']);
        Route::delete('/file/{id}', [FileController::class, 'destroy']);
        Route::post('/group', [GroupController::class, 'store']);
        Route::delete('/group/{id}', [GroupController::class, 'destroy']);
        Route::post('/group/{id}/add-users', [GroupController::class, 'addUsers']);
        Route::post('/group/{id}/add-files', [GroupController::class, 'addFiles']);
        Route::delete('/group/{groupId}/user/{userId}', [GroupController::class, 'deleteUser']);
        Route::delete('/group/{groupId}/file/{fileId}', [GroupController::class, 'deleteFile']);
    });
});

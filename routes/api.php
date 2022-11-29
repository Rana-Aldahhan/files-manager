<?php
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileOperationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    Route::get( '/files/{file}',[FileController::class, 'show'])->middleware('can:view,file');
    Route::get( '/user', function (Request $request) {  return $request->user();});

    Route::get('/owned-groups', [GroupController::class, 'ownedGroups']);
    Route::get('/group/{group}', [GroupController::class, 'show']);
    //transactional routes 
    Route::middleware('transactional')->group(
        function () {
            //TODO put here each route that updates,deletes,inserts anything
            Route::post('/file', [FileController::class, 'store']);
            Route::delete('/file/{file}', [FileController::class, 'destroy'])->middleware('can:delete,file');
            Route::post('/group', [GroupController::class, 'store']);
            Route::delete('/group/{group}', [GroupController::class, 'destroy'])->middleware('can:delete,group');
            Route::post('/group/{group}/add-users', [GroupController::class, 'addUsers'])->middleware('can:addMembers,group');
            Route::post('/group/{group}/add-files', [GroupController::class, 'addFiles'])->middleware('can:addFilesToGroup,group');
            Route::delete('/group/{group}/user/{member}', [GroupController::class, 'deleteUser'])->middleware('can:removeMember,group,member');
            Route::delete('/group/{group}/file/{file}', [GroupController::class, 'deleteFile'])->middleware('can:removeFileFromGroup,group,file');

            // file operations
            Route::middleware('jsonConverter')->group(
                function () {
                    Route::put('/files/{file}/check-in', [FileOperationController::class, 'checkin'])->middleware('can:checkIn,file');
                    Route::put('/files/bulk-check-in', [FileOperationController::class, 'bulkCheckIn'])->middleware('can:bulkCheckIn,App\Models\File');
                    Route::put('/files/{file}/check-out', [FileOperationController::class, 'checkout'])->middleware('can:checkOut,file');
                    Route::put('/files/{file}/edit-file', [FileOperationController::class, 'editFile'])->middleware('can:edit,file');
                }
            );
        }
    );
});
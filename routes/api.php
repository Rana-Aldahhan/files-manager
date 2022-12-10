<?php
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileOperationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;

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


Route::middleware('logging')->group(function () {
    //unauthenticated routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    //authenticated routes
    Route::middleware(['auth:sanctum'])->group(
        function () {
            Route::get('/files/checked-in', [FileController::class, 'getCheckedInFiles']);
            Route::get('/files/{file}/content', [FileController::class, 'showFileContent'])->middleware('can:view,file');
            Route::get('/files/{file}', [FileController::class, 'show']);
            Route::get( '/user',function (Request $request) {  return $request->user(); } );
            Route::get('/owned-groups', [GroupController::class, 'ownedGroups']);
            Route::get('/groups/{group}', [GroupController::class, 'show']);
            Route::get('/groups/{group}/members', [GroupController::class, 'getMembers']);
            Route::get('/files/{file}/history', [FileController::class, 'history'])->middleware(['can:showHistory,file']);
            Route::get('/admin/files', [FileController::class, 'index'])->middleware(['admin']);
            Route::get('/admin/groups', [GroupController::class, 'index'])->middleware(['admin']);
            Route::get('/joined-groups',[UserController::class,'getJoinedGroups'])->middleware('redirectIfAdmin');
            Route::get('/all-users',[UserController::class,'getAllUsers']);
            Route::get('/owned-files',[UserController::class,'getOwnedFiles']);
            //transactional routes 
            Route::middleware('transactional')->group(
                function () {
                    //TODO put here each route that updates,deletes,inserts anything
                    //file operations
                    Route::post('/file', [FileController::class, 'store'])->middleware('fileTracer:upload');
                    Route::delete('/files/{file}', [FileController::class, 'destroy'])->middleware('can:delete,file');
                    Route::put('/files/{file}/check-in', [FileController::class, 'checkin'])->middleware(['can:checkIn,file' , 'fileTracer:check-in']);
                    Route::put('/files/bulk-check-in', [FileController::class, 'bulkCheckIn'])->middleware(['can:bulkCheckIn,App\Models\File', 'fileTracer:bulk-check-in']);
                    Route::put('/files/{file}/check-out', [FileController::class, 'checkout'])->middleware(['can:checkOut,file', 'fileTracer:check-out']);
                    Route::put('/files/{file}/edit-file', [FileController::class, 'editFile'])->middleware(['can:edit,file', 'fileTracer:edit']);
                    // group operations
                    Route::post('/group', [GroupController::class, 'store']);
                    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->middleware('can:delete,group');
                    Route::post('/groups/{group}/add-users', [GroupController::class, 'addUsers'])->middleware('can:addMembers,group');
                    Route::post('/groups/{group}/add-files', [GroupController::class, 'addFiles'])->middleware('can:addFilesToGroup,group');
                    Route::delete('/groups/{group}/users/{member}', [GroupController::class, 'deleteUser'])->middleware('can:removeMember,group,member');
                    Route::delete('/groups/{group}/files/{file}', [GroupController::class, 'deleteFile'])->middleware('can:removeFileFromGroup,group,file');

                }
            );

        }
    );
});
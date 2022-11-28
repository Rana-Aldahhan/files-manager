<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\File;



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
    Route::get(
        '/files/{file}',
        function (File $file) {
            return response()->file(storage_path('app\public\files\\' . $file->path));
        }
    )->middleware('can:view,file');
    Route::get(
        '/user',
        function (Request $request) {
            return $request->user();
        }
    );

    //transactional routes 
    Route::middleware(['transactional', 'jsonConverter'])->group(
        function () {
            //TODO put here each route that updates,deletes,inserts anything
            Route::put('/files/{file}/check-in', [FileController::class, 'checkin'])->middleware('can:checkIn,file');
            Route::put('/files/bulk-check-in', [FileController::class, 'bulkCheckIn']); //->middleware('can:bulkCheckIn');
            Route::put('/files/{file}/check-out', [FileController::class, 'checkout']);
            Route::put('/files/{file}/edit-file', [FileController::class, 'editFile'])->middleware('can:edit,file');
            //
        }
    );

});
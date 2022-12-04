<?php

namespace App\Http\Controllers;

use App\Interfaces\FileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\FileLogRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\FileLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File as FacadeFile;

class FileController extends Controller
{
    private FileLogRepositoryInterface $fileLogRepo;
    private FileRepositoryInterface $fileRepository;
    private UserRepositoryInterface $userRepository;
    public function __construct(
        FileLogRepositoryInterface $fileLogRepo,
        FileRepositoryInterface $fileRepository,
        UserRepositoryInterface $userRepository
    )
    {

        $this->fileLogRepo = $fileLogRepo;
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'file' => 'required',
        ]);

        $time = now();
        $timestamp = str_replace([' ', ':'], '-', $time);

        $file = $this->fileRepository->create([
            'owner_id' => auth()->user()->id,
            'name' => $request->name,
            'created_at' => $time,
            'path' => $timestamp . '-' . $request->name . '.' . $request->file('file')->getClientOriginalExtension(),
        ]);
        $request->file('file')->storeAs('files', $file->path, 'public');
        return response()->json([
            'data' => $file,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $file->destroy($file->id);
        Storage::disk('public')->delete("files/" . $file->path);
        $this->fileActionLogging($file, auth(), "delete");
        return response()->json([
            'data' => [],
        ], 200);
    }

    /**
     * show the specified resource 
     *
     * @param  File $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        return response()->file(storage_path('app\public\files\\' . $file->path));
    }

    private function storeFile(Request $request)
    {
        $filePath = null;
        if ($request->hasFile('file')) {
            // Get just ext
            $extension = $request->file('file')->getClientOriginalExtension();
            // timestamp
            $time = now();
            $timestamp = str_replace([' ', ':'], '-', $time);
            // Upload file
            $filePath = $timestamp . '-' . $request->name . '.' . $extension;
            $request->file('file')->storeAs('files', $filePath, 'public');
        }
        return $filePath;
    }

    public function checkin(File $file)
    {
        $file->status = 'checkedIn';
        $file->reserver_id = auth()->id();
        $file->save();
        //return $file;
        return response()->json([
            'data' => $file,
        ], 200);

    }

    public function checkout(File $file)
    {
        $file->status = 'free';
        $file->reserver_id = null;
        $file->save();
        // return $file;
        return response()->json([
            'data' => $file,
        ], 200);
    }
    public function editFile(File $file, Request $request)
    {
        // delete the old file
        $myFile = storage_path('app\public\files\\' . $file->path);
        FacadeFile::delete($myFile);
        // upload another file 
        $fileNameToStore = $this->storeFile($request);
        $file->path = $fileNameToStore;
        $file->name = $request->name;
        $file->save();
        return response()->json([
            'data' => $file,
        ], 200);
    }

    public function bulkCheckIn(Request $request)
    {
        $bulkCheckInFiles = Cache::pull('bulkCheckInFiles');
        $bulkCheckInFiles->map(function ($file) {
            return $this->checkin($file)->getOriginalContent();
        });
        return response()->json([
            'data' => $bulkCheckInFiles,
        ], 200);
    }

    public function history(File $file)
    {
        $fileLog = $this->fileLogRepo->getFileLog($file->id)->map(function ($record) {
            $fileName = $this->fileRepository->find($record->file_id)->name;
            $userName = $this->userRepository->find($record->user_id)->name;
            return [
                'user_name' => $userName,
                'user_id' => $record->user_id,
                'file_name' => $fileName,
                'file_id' => $record->file_id,
                'action_date' => $record->created_at,
            ];
        })->values();
        return response()->json([
            'data' => $fileLog,
        ], 200);
    }

}
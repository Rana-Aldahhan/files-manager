<?php

namespace App\Http\Controllers;

use App\Interfaces\FileLogRepositoryInterface;
use App\Interfaces\FileRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\FileLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File as FacadeFile;

class FileOperationController extends Controller
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
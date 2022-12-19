<?php

namespace App\Services;

use App\Interfaces\FileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\FileLogRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class FileService extends Service
{
    public function __construct(
        private FileLogRepositoryInterface $fileLogRepo,
        private FileRepositoryInterface $fileRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /** helper method */
    private function storeFile(UploadedFile $uploadedFile, $fileName)
    {
        $extension = $uploadedFile->getClientOriginalExtension(); // Get just ext
        // timestamp
        $time = now();
        $timestamp = str_replace([' ', ':'], '-', $time);
        // Upload file
        $filePath = $timestamp . '-' . $fileName . '.' . $extension;
        $uploadedFile->storeAs('files', $filePath);
        return $filePath;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UploadedFile $uploadedFile, $fileName)
    {
        $file = $this->fileRepository->create([
            'owner_id' => auth()->user()->id,
            'name' => $fileName,
            'path' => $this->storeFile($uploadedFile, $fileName),
        ]);
        $uploadedFile->storeAs('files', $file->path);
        return $file;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        $lockedFile = $this->fileRepository->lockForUpdate($file->id);
        $lockedFile->destroy($file->id);
        Storage::disk()->delete("files/" . $file->path);
    }

    /**
     * get reserved files by the user
     */
    public function getCheckedInFiles()
    {
        $checkedInFiles = auth()->user()->reservedFiles()->get(['id', 'name', 'path', 'status']);
        return $checkedInFiles;
    }

    /**
     * show the specified resource 
     */
    public function showFileContent(File $file)
    {
        $fileContent = Storage::disk()->path('files\\') . $file->path;
        return $fileContent;
    }

    /**
     * reserve a specific file
     */
    public function checkin(File $file)
    {
        // ray()->showQueries();
        $lockedFile = $this->fileRepository->lockForUpdate($file->id);
        $lockedFile->status = 'checkedIn';
        $lockedFile->reserver_id = auth()->id();
        $lockedFile->save();
        return $lockedFile;
    }

    /**
     * Release reservation of a file
     */
    public function checkout(File $file)
    {
        $file->status = 'free';
        $file->reserver_id = null;
        $file->save();
        return $file;
    }

    /**
     * upload a new file instead of the old one after reserving it
     */
    public function editFile(File $file, UploadedFile $uploadedFile, $fileName)
    {
        Storage::disk()->delete("files/" . $file->path);  // delete the old file
        // upload another file 
        $file->path = $this->storeFile($uploadedFile, $fileName);
        $file->name = $fileName;
        $file->save();
        return $file;
    }

    /**
     * reserve a file or more
     */
    public function bulkCheckIn()
    {
        $files = Cache::pull('bulkCheckInFiles');
        $bulkCheckInFiles = $files->map(function ($file) {
            return $this->checkin($file);
        });
        return $bulkCheckInFiles;
    }

    /**
     * get a file log 
     */
    public function history(File $file)
    {
        $fileLog = $this->fileLogRepo->getFileLog($file->id)->map(function ($record) {
            $userName = $this->userRepository->find($record->user_id)->name;
            return [
                'id' => $record->id,
                'user_name' => $userName,
                'user_id' => $record->user_id,
                'action' => $record->action,
                'action_date' => $record->created_at,
            ];
        })->values();
        return $fileLog;
    }

    /**
     * get all files
     */
    public function index()
    {
        $allFiles = $this->fileRepository->all();
        return $allFiles;
    }

    /**
     * get the file info
     */
    public function show(File $file)
    {
        return $file;
    }
}

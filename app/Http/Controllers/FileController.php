<?php

namespace App\Http\Controllers;

use App\Interfaces\FileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\FileLogRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Cache;

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
        $validator = Validator::make($request->only('name', 'file'), [
            'name' => 'required',
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $time = now();
        $timestamp = str_replace([' ', ':'], '-', $time);

        $file = $this->fileRepository->create([
            'owner_id' => auth()->user()->id,
            'name' => $request->name,
            'created_at' => $time,
            'path' => $timestamp . '-' . $request->name . '.' . $request->file('file')->getClientOriginalExtension(),
        ]);
        $request->file('file')->storeAs('files', $file->path);
        return $this->successResponse($file);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $lockedFile = $this->fileRepository->lockForUpdate($file->id);
        $lockedFile->destroy($file->id);
        Storage::disk()->delete("files/" . $file->path);
        return response()->json([
            'data' => [],
        ], 200);
    }

    public function getCheckedInFiles()
    {
        return $this->successResponse(auth()->user()->reservedFiles()->get(['id', 'name', 'path', 'status']));
    }
    /**
     * show the specified resource 
     *
     * @param  File $file
     * @return \Illuminate\Http\Response
     */
    public function showFileContent(File $file)
    {
        return response()->file(Storage::disk()->path('files\\') . $file->path);
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
            $request->file('file')->storeAs('files', $filePath);
        }
        return $filePath;
    }

    public function checkin(File $file)
    {
        // ray()->showQueries();
        $lockedFile = $this->fileRepository->lockForUpdate($file->id);
        $lockedFile->status = 'checkedIn';
        $lockedFile->reserver_id = auth()->id();
        $lockedFile->save();
        return $this->successResponse($lockedFile);

    }

    public function checkout(File $file)
    {
        $file->status = 'free';
        $file->reserver_id = null;
        $file->save();
        return $this->successResponse($file);
    }
    public function editFile(Request $request,File $file)
    {
        // delete the old file
        Storage::disk()->delete("files/" . $file->path);
        // upload another file 
        $fileNameToStore = $this->storeFile($request);
        $file->path = $fileNameToStore;
        $file->name = $request->name;
        $file->save();
       
        return $this->successResponse($file);
    }

    public function bulkCheckIn(Request $request)
    {
        $bulkCheckInFiles = Cache::pull('bulkCheckInFiles');
        $bulkCheckInFiles->map(function ($file) {
            return $this->checkin($file)->getOriginalContent();
        });
        return $this->successResponse($bulkCheckInFiles);
    }

    public function history(File $file)
    {
        $fileLog = $this->fileLogRepo->getFileLog($file->id)->map(function ($record) {
            $userName = $this->userRepository->find($record->user_id)->name;
            return [
                'id'=>$record->id,
                'user_name' => $userName,
                'user_id' => $record->user_id,
                'action'=>$record->action,
                'action_date' => $record->created_at,
            ];
        })->values();
        return $this->successResponse($fileLog);
    }

    public function index()
    {
        $allFiles = $this->fileRepository->all();
        return $this->successResponse($allFiles);
    }

    public function show(File $file)
    {
        return $this->successResponse($file);
    }

}
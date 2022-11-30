<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\FileRepositoryInterface;

class FileController extends Controller
{
    private $fileRepository;

    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator =  Validator::make($request->only('name', 'file'), [
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

        $request->file('file')->storeAs('files', $file->path, 'public');
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
        $file->destroy($file->id);
        Storage::disk('public')->delete("files/" . $file->path);
        return $this->successResponse([]);
    }

    public function getCheckedInFiles()
    {
        return $this->successResponse(auth()->user()->reservedFiles()->get(['id','name','path','status']));
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
}

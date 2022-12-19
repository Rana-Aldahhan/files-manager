<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {
    }

    private function checkValidationError($validator)
    {
        $validator?->fails() ?
            throw ValidationException::withMessages([
                $validator?->errors()->first()
            ]) : null;
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('name', 'file'), [
            'name' => 'required',
            'file' => 'required',
        ]);
        //case of input validation failure
        $this->checkValidationError($validator);
        $file = $request->hasFile('file') ? $this->fileService->store($request->file('file'), $request->name) : null;
        return $this->successResponse($file);
    }

    /**
     * Remove the specified resource from storage.

     * @param File $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(File $file)
    {
        $this->fileService->destroy($file);
        return response()->json([
            'data' => [],
        ], 200);
    }

    /**
     * get reserved files by the user
     */
    public function getCheckedInFiles()
    {
        return $this->fileService->getCheckedInFiles();
    }
    /**
     * show the specified resource 
     */
    public function showFileContent(File $file)
    {
        $fileContent = $this->fileService->showFileContent($file);
        return response()->file($fileContent);
    }

    /**
     * reserve a specific file
     */
    public function checkin(File $file)
    {
        $lockedFile = $this->fileService->checkin($file);
        return $this->successResponse($lockedFile);
    }

    /**
     * Release reservation of a file
     */
    public function checkout(File $file)
    {
        $releasedFile = $this->fileService->checkout($file);
        return $this->successResponse($releasedFile);
    }

    /**
     * upload a new file instead of the old one after reserving it
     */
    public function editFile(Request $request, File $file)
    {
        $editedFile = $this->fileService->editFile($file, $request->file('file'), $request->name);
        return $this->successResponse($editedFile);
    }

    /**
     * reserve a file or more
     */
    public function bulkCheckIn(Request $request)
    {
        $bulkCheckInFiles = $this->fileService->bulkCheckIn();
        return $this->successResponse($bulkCheckInFiles);
    }

    /**
     * get a file log 
     */
    public function history(File $file)
    {
        $fileLog = $this->fileService->history($file);
        return $this->successResponse($fileLog);
    }

    /**
     * get all files
     */
    public function index()
    {
        $allFiles = $this->fileService->index();
        return $this->successResponse($allFiles);
    }
    /**
     * get the file info
     */
    public function show(File $file)
    {
        $showedFiles = $this->fileService->show($file);
        return $this->successResponse($showedFiles);
    }
}

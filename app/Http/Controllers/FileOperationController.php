<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File as FacadeFile;

class FileOperationController extends Controller
{
    use ApiResponser;
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
        return $file;
        // call actionLogging (middleware after filecheckedin)
        // call request logging (middleware after filecheckedin)
    }

    public function checkout(File $file)
    {
        $file->status = 'free';
        $file->reserver_id = null;
        $file->save();
        return $file;
        // call actionLogging (middleware after filecheckedin)
        // call request logging (middleware after filecheckedin)

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
        return $file;
        // call actionLogging (middleware after filecheckedin)
        // call request logging (middleware after filecheckedin)
    }

    public function bulkCheckIn(Request $request)
    {
        // to check that the function is working 
        /*  $canCheckAll = true;
        $files = collect();
        collect(request()->ids) //get files ids from request
        ->map(function ($id) use (&$canCheckAll, &$files) //map over them to check each file
        {
        $file = File::find($id);
        $files->push($file);
        });
        Cache::put('bulkCheckInFiles', $files);*/
        $bulkCheckInFiles = Cache::pull('bulkCheckInFiles');
        $bulkCheckInFiles->map(function ($file) {
            return $this->checkin($file);
        });
        return $bulkCheckInFiles;
    }


}
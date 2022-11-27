<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function Ramsey\Uuid\Lazy\toString;

class FileController extends Controller
{
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

        $file = File::create([
            'name' => $request->name,
            'created_at' => $time,
            'user_id' => 1,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = File::find($id);

        $file->destroy($id);
        Storage::disk('public')->delete("files/".$file->path);
        return  response()->json([
            'data' => [],
        ], 200);
    }
}

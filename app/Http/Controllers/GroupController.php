<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
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
        ]);

        $group = Group::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
        ]);

        foreach ($request->users as $user) {
            $group->members()->attach($user);
        }
        foreach ($request->filesIds as $fileId) {
            $group->files()->attach($fileId);
        }
        return response()->json([
            'data' => [],
        ], 201);
    }


    public function ownedGroups()
    {
        return auth()->user()->ownedGroups()->get();
    }


    public function addUsers(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        foreach ($request->users as $user) {
            $group->members()->attach($user);
        }
        return response()->json([
            'data' => [],
        ], 201);
    }

    public function deleteUser($groupId, $userId)
    {
        Group::findOrFail($groupId)->members()->detach($userId);


        return response()->json([
            'data' => [],
        ], 200);
    }

    public function deleteFile($groupId, $fileId)
    {
        Group::findOrFail($groupId)->files()->detach($fileId);


        return response()->json([
            'data' => [],
        ], 200);
    }

    public function addFiles(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        foreach ($request->filesIds as $fileId) {
            $group->files()->attach($fileId);
        }
        return response()->json([
            'data' => [],
        ], 201);
    }

    public function show($id)
    {
        $group = Group::with(['members', 'files'])->findOrFail($id);
        $group->members->transform(function ($member) {
            $member->setVisible(['id', 'name']);
            return $member;
        });
        $group->files->transform(function ($file) {
            $file->setVisible(['id', 'name', 'path', 'status']);
            return $file;
        });
        return $group;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Group::destroy($id);

        return  response()->json([
            'data' => [],
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Group;
use App\Models\User;
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
        //TODO add current user to members
        $request->validate([
            'name' => 'required',
        ]);

        $group = Group::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
        ]);
        collect($request->users)->map(function($member)use ($group){
            $group->members()->attach($member);
        });
        collect($request->fileIds)->map(function($fileId)use ($group){
            $group->files()->attach($fileId);
        });
        return response()->json([
            'data' => [],
        ], 201);
    }


    public function ownedGroups()
    {
        return auth()->user()->ownedGroups()->get();
    }


    public function addUsers(Request $request, Group $group)
    {
        foreach ($request->users as $user) {
            $group->members()->attach($user);
        }
        return response()->json([
            'data' => [],
        ], 201);
    }

    public function deleteUser(Group $group, User $member)
    {
        $group->members()->detach($member->id);


        return response()->json([
            'data' => [],
        ], 200);
    }

    public function deleteFile(Group $group, File $file)
    {
        $group->files()->detach($file->id);


        return response()->json([
            'data' => [],
        ], 200);
    }

    public function addFiles(Request $request, Group $group)
    {
        //TODO make it a map
        foreach ($request->filesIds as $fileId) {
            $group->files()->attach($fileId);
        }
        return response()->json([
            'data' => [],
        ], 201);
    }

    public function show(Group $group)
    {
        // $group = Group::with(['members', 'files'])->findOrFail($id);
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
     * @param  Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        Group::destroy($group->id);

        return  response()->json([
            'data' => [],
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\GroupRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class GroupController extends Controller
{
    private $groupRepository;

    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('name', 'users', 'fileIds'), [
            'name' => 'required',
            'users' => 'present|array',
            'fileIds' => 'present|array'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }
        $group = $this->groupRepository->create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
        ]);
        $group->members()->attach(auth()->user()->id);
        collect($request->users)->map(function ($member) use ($group) {
            $group->members()->attach($member);
        });
        collect($request->fileIds)->map(function ($fileId) use ($group) {
            $group->files()->attach($fileId);
        });
        return $this->successResponse($group);
    }


    public function ownedGroups()
    {
        return auth()->user()->ownedGroups()->get();
    }


    public function addUsers(Request $request, Group $group)
    {
        $validator = Validator::make($request->only('users'), [
            'users' => 'present|array'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }
        collect($request->users)->map(function ($user) use ($group) {
             $group->members()->syncWithoutDetaching($user);
        });
        return $this->successResponse($group->members()->get());
    }

    public function deleteUser(Group $group, User $member)
    {
        $group->members()->detach($member->id);
        return $this->successResponse([]);
    }

    public function deleteFile(Group $group, File $file)
    {
        $group->files()->detach($file->id);
        return $this->successResponse([]);
    }

    public function addFiles(Request $request, Group $group)
    {
        $validator = Validator::make($request->only('filesIds'), [
            'filesIds' => 'present|array'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }
        collect($request->filesIds)->map(function ($fileId) use ($group) {
            $group->files()->syncWithoutDetaching($fileId);
        });
        return $this->successResponse($group->files()->get());
    }

    public function show(Group $group)
    {
        // $group = Group::with(['members', 'files'])->findOrFail($id);
        $cachedGroup = Cache::rememberForever($group->id, function () use ($group) {
            $group->members->transform(
                function ($member) {
                    $member->setVisible(['id', 'name']);
                    return $member;
                }
            );
            $group->files->transform(
                function ($file) {
                    $file->setVisible(['id', 'name', 'path', 'status']);
                    return $file;
                }
            );
            return $group;
        });
       return $this->successResponse($cachedGroup);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        $this->groupRepository->delete($group->id);
        return $this->successResponse([]);
    }

    public function index()
    {
        $allGroups = $this->groupRepository->all();
        return $this->successResponse($allGroups);
    }

    public function getMembers(Group $group){
        return $this->successResponse($group->members()->get());
    }
}
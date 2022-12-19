<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use App\Models\Group;
use App\Interfaces\GroupRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class GroupService extends Service
{

    public function __construct(private GroupRepositoryInterface $groupRepository)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($name, $users, $filesIds)
    {
        $group = $this->groupRepository->create([
            'user_id' => auth()->user()->id,
            'name' => $name,
        ]);
        $group->members()->attach(auth()->user()->id);
        collect($users)->map(function ($member) use ($group) {
            $group->members()->syncWithoutDetaching($member);
        });
        collect($filesIds)->map(function ($fileId) use ($group) {
            $group->files()->attach($fileId);
        });
        return $group;
    }


    public function ownedGroups()
    {
        return auth()->user()->ownedGroups()->with(['files.reserver'])->get();
    }


    public function addUsers(Group $group, $users)
    {
        collect($users)->map(function ($user) use ($group) {
            $group->members()->syncWithoutDetaching($user);
        });
        return $group->members()->get();
    }

    public function deleteUser(Group $group, User $member)
    {
        $group->members()->detach($member->id);
    }

    public function deleteFile(Group $group, File $file)
    {
        $group->files()->detach($file->id);
    }

    public function addFiles(Group $group, $filesIds)
    {
        collect($filesIds)->map(function ($fileId) use ($group) {
            $group->files()->syncWithoutDetaching($fileId);
        });
        return $group->files()->get();
    }

    public function show(Group $group)
    {
        $groupInfo = Cache::rememberForever($group->id, function () use ($group) {
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
        return $groupInfo;
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
    }

    public function index()
    {
        $allGroups = $this->groupRepository->all();
        return $allGroups;
    }

    public function getMembers(Group $group)
    {
        return $group->members()->get();
    }
}

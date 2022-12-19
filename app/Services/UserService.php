<?php

namespace App\Services;

use App\Interfaces\GroupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserService extends Service
{

    public function __construct(
        private UserRepositoryInterface $userRepo,
        private GroupRepositoryInterface $groupRepo
    ) {
    }

    public function getJoinedGroups()
    {
        $groups = auth()->user()->joinedGroups()->with(['files.reserver', 'members'])->get();
        $groups->push($this->groupRepo->find(1));
        $groups = $groups->sortBy('id')->values();
        return $groups;
    }
    public function getOwnedFiles()
    {
        $files = auth()->user()->ownedFiles()->with(['reserver'])->get();
        return $files;
    }
    public function getAllUsers()
    {
        return $this->userRepo->all();
    }
}

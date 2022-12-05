<?php

namespace App\Http\Controllers;

use App\Interfaces\GroupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userRepo;
    private $groupRepo;

    public function __construct(UserRepositoryInterface $userRepo,
    GroupRepositoryInterface $groupRepo){
        $this->userRepo=$userRepo;
        $this->groupRepo=$groupRepo;
    }

    public function getJoinedGroups()
    {
        $groups=auth()->user()->joinedGroups()->with(['files.reserver','members'])->get();
        $groups->push($this->groupRepo->find(1));
        return $this->successResponse($groups);
    }
    public function getOwnedFiles()
    {
        $files=auth()->user()->ownedFiles()->get();
        return $this->successResponse($files);
    }
    public function getAllUsers(){
        return $this->successResponse($this->userRepo->all());
    }
}

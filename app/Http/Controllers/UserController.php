<?php

namespace App\Http\Controllers;

use App\Interfaces\GroupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{


    public function __construct(private UserService $userService)
    {
    }

    public function getJoinedGroups()
    {
        $groups = $this->userService->getJoinedGroups();
        return $this->successResponse($groups);
    }
    public function getOwnedFiles()
    {
        $files = $this->userService->getOwnedFiles();
        return $this->successResponse($files);
    }
    public function getAllUsers()
    {
        $allUsers = $this->userService->getAllUsers();
        return $this->successResponse($allUsers);
    }
}

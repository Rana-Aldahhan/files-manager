<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\GroupRepositoryInterface;
use App\Services\GroupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{

    public function __construct(private GroupService $groupService)
    {
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
        $validator = Validator::make($request->only('name', 'users', 'fileIds'), [
            'name' => 'required',
            'users' => 'present|array',
            'fileIds' => 'present|array'
        ]);
        $this->checkValidationError($validator);
        $group = $this->groupService->store($request->name, $request->users, $request->fileIds);
        return $this->successResponse($group);
    }

    public function ownedGroups()
    {
        return $this->groupService->ownedGroups();
    }

    public function addUsers(Request $request, Group $group)
    {
        $validator = Validator::make($request->only('users'), [
            'users' => 'present|array'
        ]);
        $this->checkValidationError($validator);
        $groupMembers = $this->groupService->addUsers($group, $request->users);
        return $this->successResponse($groupMembers);
    }

    public function deleteUser(Group $group, User $member)
    {
        $this->groupService->deleteUser($group, $member);
        return $this->successResponse([]);
    }

    public function deleteFile(Group $group, File $file)
    {
        $this->groupService->deleteFile($group, $file);
        return $this->successResponse([]);
    }

    public function addFiles(Request $request, Group $group)
    {
        $validator = Validator::make($request->only('filesIds'), [
            'filesIds' => 'present|array'
        ]);
        $this->checkValidationError($validator);
        $groupFiles = $this->groupService->addFiles($group, $request->filesIds);
        return $this->successResponse($groupFiles);
    }

    public function show(Group $group)
    {
        $groupInfo = $this->groupService->show($group);
        return $this->successResponse($groupInfo);
    }

    /**
     * Remove the specified resource from storage.
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Group $group)
    {
        $this->groupService->destroy($group);
        return $this->successResponse([]);
    }

    public function index()
    {
        $allGroups = $this->groupService->index();
        return $this->successResponse($allGroups);
    }

    public function getMembers(Group $group)
    {
        $groupMembers = $this->groupService->getMembers($group);
        return $this->successResponse($groupMembers);
    }
}

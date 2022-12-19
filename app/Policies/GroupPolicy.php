<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Group;
use App\Models\File;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Determine if the given group can be deleted by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group $group
     * @return bool
     * use it in routes like: ->middlware('can:delete,group') and use model binding 
     */
    public function delete(User $user, Group $group)
    {
        $groupHasReservedFiles=false;
        $group->files->map(function($file)use(&$groupHasReservedFiles,$group){
            if($file->isReserved() && $group->members->find($file->reserver_id)!=null)//case the file is reserved by a group member
                $groupHasReservedFiles=true;
        });
        return $user->isGroupOwner($group) && !$groupHasReservedFiles
        ? Response::allow()
        : Response::deny('You can not delete this group');
    }
    /**
     *Determine if the current user can add new members to the current group
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group $group
     * @return bool
     * use it in routes like: ->middlware('can:addMembers,group') and use model binding 
     */
    public function addMembers(User $user, Group $group)
    {
        return $user->isGroupOwner($group)
        ? Response::allow()
        : Response::deny('You can not add members to this group');
    }
    /**
     *Determine if the current user can remove a member in the current group
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group $group
     * @return bool
     * use it in routes like: ->middlware('can:removeMember,group,member') and use model binding 
     */
    public function removeMember(User $user, Group $group,User $member)
    {
        $memberHasReservedGroupFile=false;
        $group->files->map(function($file)use(&$memberHasReservedGroupFile,$member){
            if($file->reserver_id==$member->id)
                $memberHasReservedGroupFile=true;
        });
        return $user->isGroupOwner($group) && !$memberHasReservedGroupFile
        ? Response::allow()
        : Response::deny('You can not remove members from this group');
    }
   /**
     *Determine if the current user can add new files to the current group
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group $group
     * @return bool
     * use it in routes like: ->middlware('can:addFilesToGroup,group') and use model binding 
     */
    public function addFilesToGroup(User $user, Group $group)
    {
        $userIsOwnerofAllFile=true;
        collect(request()->filesIds)//get files ids from request
        ->map(function($id)use(&$userIsOwnerofAllFile,$user)//map over them to check each file
        {
            $file=File::find($id);
            if(!$user->isFileOwner($file))
             $userIsOwnerofAllFile=false;
        });
        return $userIsOwnerofAllFile
               && ($user->isMemberOfGroup($group) || $group->isPublicGroup())
               ? Response::allow()
               : Response::deny('You can not add files to this group');
    }
    /**
     *Determine if the current user can remove a file from the current group
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group $group
     * @param  \App\Models\File $file
     * @return bool
     * use it in routes like: ->middlware('can:removeFileFromGroup,group,file') and use model binding 
     */
    public function removeFileFromGroup(User $user, Group $group,File $file)
    {
        return $user->isFileOwner($file)
               && ($user->isMemberOfGroup($group) || $group->isPublicGroup())
               && ($file->isFree() || $file->reserver_id=$user->id)
               ? Response::allow()
               : Response::deny('You can not remove files from this group');
    }
    
}

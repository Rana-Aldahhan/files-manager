<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Cache;

class FilePolicy
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
     * Determine if the given file can be viewed by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File $file
     * @return bool
     * use it in routes like: ->middlware('can:view,file') and use model binding 
     */
    public function view(User $user, File $file)
    {
        return $user->hasReservedFile($file) //case the file is reserved by the user
            || ($file->isFree() && $user->hasAccessToFile($file)) //case the file is free and the user has access to it    
            ? Response::allow()
            : Response::deny('You can not view this file');       
    }
    /**
     * Determine if the given file can be checked-in by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File $file
     * @return bool
     * use it in routes like: ->middlware('can:checkIn,file') and use model binding 
     */
    public function checkIn(User $user, File $file)
    {
        return $file->isFree() && $user->hasAccessToFile($file) //file is free and the user has access to it
        ? Response::allow()
        : Response::deny('You can not check-in this file');            
    }
    /**
     * Determine if a collection of given files can be checked-in by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     * use it in routes like: ->middlware('can:bluckCheckIn,App\Models\File')
     */
    public function bulkCheckIn(User $user)
    {
        $canCheckAll = true;
        $files = collect();
        collect(request()->ids)->unique() //get files ids from request
            ->map(function ($id) use (&$canCheckAll, $user, &$files) //map over them to check each file
            {
                $file = File::find($id);
                $files->push($file);
                if (!$this->checkIn($user, $file)) //check a single file using the previously defined policy method "checkIn"
                    $canCheckAll = false;
            });
        Cache::put('bulkCheckInFiles', $files);
        return $canCheckAll? Response::allow()
        : Response::deny('You can not check-in selected file'); 
    }

    /**
     * Determine if the given file can be deleted by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File $file
     * @return bool
     * use it in routes like: ->middlware('can:delete,file') and use model binding 
     */
    public function delete(User $user, File $file)
    {
        return $file->isFree() && $user->isFileOwner($file) //file is free and user is file's owner    
                ? Response::allow()
                : Response::deny('You can not delete this file');          
    }

    public function edit(User $user, File $file)
    {
        return $file->reserver_id == $user->id
        ? Response::allow()
        : Response::deny('You can not edit this file'); 
    }
    public function checkOut(User $user, File $file)
    {
        return $file->reserver_id == $user->id
        ? Response::allow()
        : Response::deny('You can not check-out this file'); 
    }
    public function showHistory(User $user, File $file)
    {
        return $user->isFileOwner($file) || $user->isAdmin()
        ? Response::allow()
        : Response::deny('You can not view history of this file'); 
    }
    public function create(User $user)
    {
        return $user->ownedFiles()->get()->collect()->count() < config('file.max_user_files')
        ? Response::allow()
        : Response::deny('You can not upload more files'); 
    }
}

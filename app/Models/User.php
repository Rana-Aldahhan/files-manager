<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * relations
     */
    public function ownedGroups()
    {
        return $this->hasMany(Group::class);
    }
    public function ownedFiles()
    {
        return $this->hasMany(File::class, 'owner_id');
    }
    public function reservedFiles()
    {
        return $this->hasMany(File::class, 'reserver_id');
    }
    public function joinedGroups()
    {
        return $this->belongsToMany(Group::class);
    }
    /**
     * methods
     */
    public function hasAccessToFile(File $file) //a user has access to file in his joined groups or in a public group

    {
        $fileInUserJoinedGroups = false;
        $this->joinedGroups()->get()->map(function ($group) use ($file, &$fileInUserJoinedGroups) {
            if ($group->files()->find($file->id) != null)
                $fileInUserJoinedGroups = true;
        });
        $fileInPublicGroup = false;
        $publicGroup = Group::find(1);
        if ($publicGroup->files()->find($file->id) != null)
            $fileInPublicGroup = true;
        return $fileInUserJoinedGroups || $fileInPublicGroup || $this->isFileOwner($file);
    }
    public function hasReservedFile(File $file)
    {
        return $file->resever_id == $this->id;
    }
    public function isMemberOfGroup(Group $group)
    {
        return $this->joinedGroups()->get()->find($group->id) != null;
    }
    public function isGroupOwner(Group $group)
    {
        return $this->id == $group->owner_id;
    }
    public function isFileOwner(File $file)
    {
        return $this->id == $file->owner_id;
    }
}
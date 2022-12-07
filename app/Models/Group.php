<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'pivot'];
    // protected $with = ['files:id,name', 'members:name,id'];



    /**
     * relations
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class)->using(GroupUser::class);
    }
    public function files()
    {
        return $this->belongsToMany(File::class)->using(GroupFile::class);
    }
    /**
     * methods
     */
    public function isPublicGroup()
    {
        return $this->id == 1 || $this->name == 'public';
    }
}
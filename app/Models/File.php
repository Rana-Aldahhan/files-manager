<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'owner_id',
        'path',
        'created_at',
        'reserver_id',
    ];

    protected $hidden = [
        'pivot',
    ];


    /**
     * relations
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function reserver()
    {
        return $this->belongsTo(User::class, 'reserver_id');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
    public function logs()
    {
        return $this->hasMany(FileLog::class);
    }
    /**
     * methods
     */
    public function isFree()
    {
        return $this->status == 'free' && $this->reserver_id == null;
    }
    public function isReserved()
    {
        return $this->status == 'checkedIn' && $this->reserver_id != null;
    }


}
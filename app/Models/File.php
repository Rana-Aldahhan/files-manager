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
        'user_id',
        'path',
        'created_at',
    ];

    protected $attributes = [
    'status' => 'free',
    ];


    /**
     * relations
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
    public function logs()
    {
        return $this->hasMany(FileLog::class);
    }
 
}

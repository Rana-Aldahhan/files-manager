<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    /**
     * relations
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class);
    }
    public function files()
    {
        return $this->belongsToMany(File::class);
    }

}

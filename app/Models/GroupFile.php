<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupFile extends Pivot
{
    use HasFactory;

    protected $table = 'file_group';

    public $timestamps = null;
}
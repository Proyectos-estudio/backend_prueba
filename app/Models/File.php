<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'task_id',
        'created_by',
        'updated_by',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}

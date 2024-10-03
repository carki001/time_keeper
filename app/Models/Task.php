<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Task extends Model
{
    protected $fillable = [

        'name',
        "is_active",
        "project_id",
        "last_worktime_date",
        "user_id"
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }

    public function worktimes()
    {
        return $this->hasMany(Worktime::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

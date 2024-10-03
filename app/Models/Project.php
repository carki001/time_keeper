<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class Project extends Model
{
    protected $fillable = [

        'name',
        "is_active",
        "team_id"
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }
}

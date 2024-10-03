<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Team extends Model
{
    protected $fillable = [

        'name',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')->withPivot('work_role', 'is_user_active')->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'team_id');
    }
}

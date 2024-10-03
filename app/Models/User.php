<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use DateTimeInterface;

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
        'role',
        'preferred_timezone',
        'preferred_language',
        'country',
        'city',
        'postalcode',
        'address',
        'phone',
        'company',
        'vat',
        'avatar'


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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }


    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id')->withPivot('work_role', 'is_user_active')->withTimestamps();
    }

    public function worktimes()
    {
        return $this->hasMany(Worktime::class, 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }
}

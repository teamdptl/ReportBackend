<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use CrudTrait;
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'student_code',
        'email',
        'password',
        'is_active'
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
        'password' => 'hashed',
    ];

    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role' , 'users_id', 'roles_id');
    }

    public function feedbacks() : HasMany
    {
        return $this->hasMany(Feedback::class, 'users_id', 'id');
    }

    // Relation này dùng cho user
    public function reports() : HasMany
    {
        return $this->hasMany(Report::class, 'users_id', 'id');
    }

    // Relation này dùng cho staff
    public function jobs() : HasMany
    {
        return $this->hasMany(Assignment::class, 'worker_id', 'id');
    }

    // Relation này dùng cho manager
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'manager_id', 'id');
    }

    //Relation dùng để lấy report cho worker (Chưa dùng được huhu)
    public function reportWorker(): HasManyThrough
    {
        return $this->hasManyThrough(Report::class, Assignment::class, 'worker_id', 'id', 'id', 'reports_id');
    }

    public function isManager():bool {
        return $this->tokenCan('manager');
    }

    public function isWorker():bool {
        return $this->tokenCan('worker');
    }

    public function isUser():bool {
        return $this->tokenCan('user');
    }

    public function isAdmin():bool{
        return $this->tokenCan('admin');
    }

}

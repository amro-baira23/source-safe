<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable , HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];



    public function groups()
    {
        return $this->BelongsToMany(groups::class,'perm');
    }

    public function file_locks()
    {
        return $this->BelongsToMany(files::class,'locks');
    }

    public function file_versions()
    {
        return $this->BelongsToMany(files::class,'versions');
    }

    public function locks()
    {
        return $this->hasMany(locks::class, 'user_id');
    }

    public function versions()
    {
        return $this->hasMany(versions::class, 'user_id');
    }
    
    public function perms()
    {
        return $this->hasMany(perm::class, 'user_id');
    }


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
}

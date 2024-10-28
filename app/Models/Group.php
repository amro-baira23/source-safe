<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function files()
    {
        return $this->hasMany(File::class, 'group_id');
    }


    public function users()
    {
        return $this->belongsToMany(User::class , 'perms')->withPivot('role', 'approved')->withTimestamps();
    }
}

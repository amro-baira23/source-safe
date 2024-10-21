<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class files extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_id',
        'size',
        'type',
    ];

    public function group()
    {
        return $this->belongsTo(groups::class, 'group_id');
    }

    public function user_locks()
    {
        return $this->BelongsToMany(User::class,'locks');
    }

    public function user_versions()
    {
        return $this->BelongsToMany(User::class,'versions');
    }

    public function locks()
    {
        return $this->hasMany(locks::class, 'file_id');
    }

    public function versions()
    {
        return $this->hasMany(versions::class, 'file_id');
    }

}

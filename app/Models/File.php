<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory ,SoftDeletes ;

    protected $fillable = [
        'name',
        'group_id',
        'path',
        'active',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
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
        return $this->hasMany(Lock::class, 'file_id');
    }


}

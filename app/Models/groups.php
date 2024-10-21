<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class groups extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function files()
    {
        return $this->hasMany(files::class, 'group_id');
    }


    public function users()
    {
        return $this->BelongsToMany(User::class,'perm');
    }
}

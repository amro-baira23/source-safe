<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class perm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'role',
    ];


    public function group()
    {
        return $this->belongsTo(groups::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class locks extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_id',
        'status',
        'date',
    ];

    public function file()
    {
        return $this->belongsTo(files::class, 'file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

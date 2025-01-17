<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class Lock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_id',
        'type',
        'size',
        'Version_number',
        'status',
        'date',
        'change',
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFileContent(){
        $group = $this->file->group;
        $basename = Str::of(basename($this->file->name))->beforeLast(".");
        $path = "/projects_files/$group->name$group->id/{$basename}__$this->Version_number.$this->type";
        return Storage::get($path);
    }

    protected function serializeDate($date) {
        return $date->format("Y-m-h H:i");
    }
}

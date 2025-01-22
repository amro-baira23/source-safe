<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory ,SoftDeletes,Prunable ;

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

    public function getFullPath($version){
        $path = storage_path("app/projects_files/{$this->group->name}{$this->group->id}/$this->getBasename()" . "__$version");
        if (Storage::fileExists($path))
            return $path;
        return null;
    }

    public function locks()
    {
        return $this->hasMany(Lock::class, 'file_id');
    }

    public function getBasename(){
        return Str::of($this->name)->beforeLast(".");
    }
    public function prunable()
    {
        $paths = Storage::allFiles("projects_files");

        foreach ($paths as $path){
            $names[] = Str::of(basename($path))->beforeLast("__");
        }
        return static::whereNotIn("path",$names);
    }

}

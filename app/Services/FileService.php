<?php

namespace App\Services;

use App\Http\Requests\FileRequest;
use App\Models\File;
use App\Models\Lock;
use App\Models\Group;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\URL;

class FileService
{
public function store_file(Request $request): array
    {
        $group = $request->group;

        $is_admin = $group->users()
                        ->where('user_id', auth()->id())
                        ->where('role', 'admin')
                        ->exists();

        $file_storage_name  = Str::random(40);
        $file = $request->file("path");

        $file->storeAs("projects_files/" . ($group->name . $group->id) , $file_storage_name . "__1" .".". $file->guessExtension());

        if (!$is_admin) {
            $file = File::create([
                'name'=> $file->getClientOriginalName(),
                'path'=> $file_storage_name,
                'group_id'=> $group->id,
                'active' => 0,
            ]);

            return [
                'file' => $file,
                'message' => ' File created successfully by member ',
            ];
        } 
    
        $file = File::create([
        'name'=> $file->getClientOriginalName(),
        'path'=>$file_storage_name,
        'group_id'=>$group->id,
        'active' => 1,
        ]);

        Lock::create([
            'user_id' => auth()->user()->id,
            'file_id' => $file->id,
            'status' => 0 ,
            'type' => $request['path']->extension(),
            'size' => $request['path']->getsize(),
            'Version_number' => 1,
            'date'=> now(),
        ]);

        return [
            'file' => $file,
            'message' => 'File created successfully by admin ',
        ];

    }


    public function download($request){
        $file = $request->file;
        $group = $request->group;
        $last_version = $request->version ?? $file->locks()->orderBy("created_at")->first();
        $file_name = "app/projects_files/" . $group->name . $group->id . "/{$file->path}__{$last_version->Version_number}.{$last_version->type}";
        return storage_path($file_name);
    } 

    

}


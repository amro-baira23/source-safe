<?php

namespace App\Services;

use App\Models\File;
use App\Models\Lock;
use App\Models\Group;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class FileService
{
public function store_file(Request $request): array
    {

        $group = Group::find($request['group_id']);

        $admin = $group->users()->where('user_id', auth()->id())->where('role', 'admin')->first();

                    $filename = '';
                    if($request->hasFile('path')){
                        $filename  = $request->file('path')->store('public/files');
                    }
            if (!$admin) {

                $file = File::query()->create([
                    'name'=>$request['name'],
                    'path'=>$filename,
                    'group_id'=>$request['group_id'],
                    'active' => 0,
                    ]);
                    return [

                        'file' => $file,
                        'message' => ' File created successfully by member ',
                        ];
            }else{
                $file = File::query()->create([
                'name'=>$request['name'],
                'path'=>$filename,
                'group_id'=>$request['group_id'],
                'active' => 1,
                ]);

                $filelocks = Lock::query()->create([
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

    }

    

}


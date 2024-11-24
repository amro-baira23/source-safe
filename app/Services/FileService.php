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


    public function check_in(Request $request): array
    {
        $fileIds = $request->file_ids;
        $userId = auth()->id();
        $selectedVersions = $request->input('versions');

        foreach ($fileIds as $fileId) {
            $version = $selectedVersions[$fileId] ?? null;
            if (!Lock::where('file_id', $fileId)->where('Version_number', $version)->exists()) {
                return ['message' => "The specified version {$version} does not exist for file with ID {$fileId}"];
            }
        }

        $files = File::whereIn('id', $fileIds)
                    ->where('status', 0)
                    ->where('active', 1)
                    ->get();

        if ($files->count() !== count($fileIds)) {
            return [
                'files' => null,
                'message' => 'One or more files are either reserved or not approved by admin'
            ];
        }

        $downloadedFiles = [];
        foreach ($files as $file) {
            $requestedVersion = $selectedVersions[$file->id] ?? null;

            $versionRecord = Lock::where('file_id', $file->id)
                                ->where('Version_number', $requestedVersion)
                                ->first();

            if (!$versionRecord) {
                return [
                    'files' => null,
                    'message' => "Requested version {$requestedVersion} does not exist for file {$file->name}"
            ];
            }

            $storagePath = "projects_files/" . ($file->group->name . $file->group->id) . "/" . $file->path . "__" . $requestedVersion . '.' . $versionRecord->type;

            $file->status = 1;
            $file->save();

        Lock::create([
            'user_id' => $userId,
            'file_id' => $file->id,
            'status' => 1,  // check_in status
            'type'=> $versionRecord->type,
            'size' => $versionRecord->size,
            'Version_number' => $requestedVersion,
            'date' => now(),
        ]);

            $downloadedFiles[] = $storagePath;
        }

        return [
            'message' => 'Files downloaded successfully',
            'files' => $downloadedFiles,
        ];
    }


    public function check_out(Request $request): array
    {

        $fileIds = $request->file_ids;
        $userId = auth()->id();
        $uploadedFiles = $request->file('files');
        $files = File::whereIn('id', $fileIds)->where('status', 1)->get();

        $filesName = [];
        foreach ($files as $file) {
            $lastLock = Lock::where('file_id', $file->id)->latest()->first();

            if (!$lastLock || $lastLock->user_id !== $userId || $lastLock->status !== 1) {
                return [
                    'files' =>  null,
                    'message' => 'One or more files are not checked in by this user'];
            }

            $fileBaseName = pathinfo($file->name, PATHINFO_FILENAME);

            //  search in uploadfiles where the name is same in the main files
            $uploadedFile = collect($uploadedFiles)->first(function ($upload) use ($fileBaseName) {
                return pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME) === $fileBaseName;
            });
            if (!$uploadedFile) {
                return [
                    'files' =>  null,
                    'message' => "Uploaded file name does not match file {$file->name} in the system"
                ];
            }

            $file->status = 0;
            $file->save();

            Lock::create([
                'user_id' => $userId,
                'file_id' => $file->id,
                'status' => 0,  // check_out status
                'type' => $uploadedFile->extension(),
                'size' => $uploadedFile->getSize(),
                'Version_number' => $lastLock->Version_number + 1,
                'date' => now(),
            ]);

            $storagePath = "projects_files/" . ($file->group->name . $file->group->id);
            $uploadedFile->storeAs($storagePath, $file->path . '.' . $uploadedFile->extension());

            $filesName [] = $file->name;
        }

        return [
            'files' => $filesName ,
            'message' => 'Files successfully checked out'
        ];
    }


    public function getAvailableFilesWithVersions($fileId)
    {

        // $filesWithVersions = File::with(['locks' => function($query) {
        //     $query->orderBy('Version_number', 'desc');
        // }])->where('active', 1)->get();

        // return [
        //     'files' => $filesWithVersions ,
        //     'message' => 'all active files with versions',
        // ];

    $file = File::with(['locks' => function ($query) {
        $query->select('file_id', 'Version_number', 'type')
            ->orderBy('Version_number', 'desc')
            ->distinct('Version_number');
    }])
    ->where('id', $fileId)
    ->where('active', 1)
    ->first();

    // التحقق إذا كان الملف موجودًا
    if (!$file) {
        return [
            'files' => null,
            'message' => 'File not found or not active',
        ];
    }

    return [
        'file' => $file,
        'message' => 'Available versions for the specified file',
    ];
    }



}


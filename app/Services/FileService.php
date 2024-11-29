<?php

namespace App\Services;

use App\Http\Requests\FileRequest;
use App\Models\File;
use App\Models\Lock;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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


    public function check_in(array $files): array
    {
        $userId = auth()->id();
        $fileIds = collect($files)->pluck('file_id');
        $downloadedFilePaths = [];

        try {
            DB::transaction(function () use ($files, $fileIds, $userId, &$downloadedFilePaths) {
                $fileRecords = File::whereIn('id', $fileIds)
                    ->where('status', 0)
                    ->where('active', 1)
                    ->lockForUpdate()
                    ->get();

                if ($fileRecords->count() !== count($fileIds)) {
                    throw new \Exception('One or more files are either reserved or not approved by admin.');
                }

                foreach ($files as $file) {
                    $fileId = $file['file_id'];
                    $version = $file['version'];

                    // Find the specific file and version
                    $fileRecord = $fileRecords->where('id', $fileId)->first();
                    $versionRecord = Lock::where('file_id', $fileId)
                        ->where('Version_number', $version)
                        ->first();

                    if (!$versionRecord) {
                        throw new \Exception("Requested version {$version} does not exist for file {$fileRecord->name}.");
                    }

                    $storagePath = "projects_files/" . ($fileRecord->group->name . $fileRecord->group->id) . "/" . $fileRecord->path . "__" . $version . '.' . $versionRecord->type;
                    
                    if (Storage::exists($storagePath)) {
                        $downloadedFilePaths[] = $storagePath;
                    }

                    $fileRecord->status = 1;
                    $fileRecord->save();

                    Lock::create([
                        'user_id' => $userId,
                        'file_id' => $fileId,
                        'status' => 1,
                        'type' => $versionRecord->type,
                        'size' => $versionRecord->size,
                        'Version_number' => $version,
                        'date' => now(),
                    ]);
                }
            });
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'files' => null,
                'zip_path' => null,
            ];
        }

        $zipFileName = 'files_' . now()->timestamp . '.zip';
        $zipFilePath = $this->downloadFilesAsZip($downloadedFilePaths, $zipFileName);

        return [
            'message' => 'Files downloaded successfully',
            'zip_path' => $zipFilePath,
        ];
    }


    public function downloadFilesAsZip(array $filePaths, string $zipFileName): string
    {
        $zipPath = storage_path("app/Downloads/{$zipFileName}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($filePaths as $filePath) {
                if (Storage::exists($filePath)) {
                    $zip->addFile(Storage::path($filePath), basename($filePath));
                }
            }
            $zip->close();
        } else {
            throw new \Exception("Could not create ZIP file.");
        }

        return $zipPath;
    }

    public function check_out(Request $request): array
    {
        $fileId = $request->input('file_id');
        $userId = auth()->id();
        $uploadedFile = $request->file('file');

        $file = File::where('id', $fileId)->where('status', 1)->first();
        if (!$file) {
            return [
                'files' => null,
                'message' => 'The selected file is either not reserved or not active',
            ];
        }


        $lastLock = Lock::where('file_id', $file->id)->latest()->first(); //  the same user check_in
        if (!$lastLock || $lastLock->user_id !== $userId || $lastLock->status !== 1) {
            return [
                'files' => null,
                'message' => 'You do not have permission to check out this file',
            ];
        }

        // التحقق من أن اسم الملف المرفوع يطابق اسم الملف في النظام
        $fileBaseName = pathinfo($file->name, PATHINFO_FILENAME);
        if (pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) !== $fileBaseName) {
            return [
                'files' => null,
                'message' => "Uploaded file name does not match the file name in the system ({$file->name})",
            ];
        }

        $file->status = 0;
        $file->save();

        Lock::create([
            'user_id' => $userId,
            'file_id' => $file->id,
            'status' => 0, // check-out status
            'type' => $uploadedFile->extension(),
            'size' => $uploadedFile->getSize(),
            'Version_number' => $lastLock->Version_number + 1,
            'date' => now(),
        ]);

        $storagePath = "projects_files/" . ($file->group->name . $file->group->id);
        $uploadedFile->storeAs($storagePath, $file->path . '__'. ($lastLock->Version_number + 1) . '.' . $uploadedFile->extension());

        return [
            'files' => [$file->name],
            'message' => 'File successfully checked out',
        ];
    }

    public function getAvailableFilesWithVersions($groupId, $fileId)
    {
        $group = Group::find($groupId);

        if (!$group) {
            return [
                'versions' => null,
                'message' => 'Group not found.',
            ];
        }

        $file = File::where('id', $fileId)
                    ->where('group_id', $groupId)
                    ->where('active', 1)
                    ->first();

        if (!$file) {
            return [
                'versions' => null,
                'message' => 'File not found or not active in this group.',
            ];
        }

        $versions = $file->locks()->orderBy('Version_number', 'desc')->get();

        $uniqueVersions = $versions->unique('Version_number');

        if ($uniqueVersions->isEmpty()) {
            return [
                'versions' => null,
                'message' => 'No versions found for this file',
            ];
        }

        return [
            'versions' => $uniqueVersions,
            'message' => 'Available versions for the specified file',
        ];
    }


    public function getGroupFiles(Group  $group): array
    {
        $files =  $group->files()->get();

        return [
            'files' => $files,
            'message' => 'This all files for this group',
        ];
    }


    public function getAllFiles()
    {
        return File::all();
    }


    public function deleteFileWithLocks(File $file): array
    {

        DB::transaction(function () use ($file) {
            $file->locks()->delete();
            $file->delete();
        });

        return [
            'file' => $file,
            'message' => 'File and its locks deleted successfully.'
        ];
    }


    public function softDeleteFile(File $file): array
    {
        $file->delete();

        return [
            'file' => $file,
            'message' => 'File soft deleted successfully.'
        ];
    }




}


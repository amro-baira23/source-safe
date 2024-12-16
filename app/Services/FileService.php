<?php

namespace App\Services;

use App\Http\Repositories\FileRepository;
use App\Http\Resources\FileResource;
use App\Http\Resources\LockResource;
use App\Jobs\TrackFileChanges;
use App\Models\File;
use App\Models\Lock;
use App\Models\Group;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Event\Code\Throwable;
use ZipArchive;


class FileService
{
    private FileRepository $fileRepositry;

    public function __construct()
    {
        $this->fileRepositry = new FileRepository();
    }

    public function indexPerGroup($request, $group){
        $files = $this->fileRepositry->indexPerGroup($request,$group);
        return [
            "files" => FileResource::collection($files),
            "message" => "group \"$group->name\" files"
        ];
    }

    public function store_file(Request $request, Group $group): array {

        $is_admin = $group->isAdmin(auth()->user());

        $file_storage_name  = Str::random(40);
        $file = $request->file("path");

        $file->storeAs("projects_files/" . ($group->name . $group->id) , $file_storage_name . "__1" .".". $file->guessExtension());

        if (!$is_admin) {
            $file = $this->fileRepositry->store($file->getClientOriginalName(), $file_storage_name, $group->id);
            return [
                'file' => $file,
                'message' => ' File created successfully by member ',
            ];
        }

        $file = $this->fileRepositry->store($file->getClientOriginalName(), $file_storage_name, $group->id, 1);

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


    public function download($group, $file, $required_version = null){
        if(is_null($required_version)) {
            $required_version = $file->locks()->orderBy("created_at","desc")->first()->Version_number;
            $file_type = $file->locks()->orderBy("created_at","desc")->first()->type;
        } else {
            $file_type = $file->locks()
            ->where("file_id",$file->id)
            ->orderBy("created_at","desc")->first()->type;
        }
        $file_name = "projects_files/$group->name$group->id/{$file->path}__{$required_version}.{$file_type}";
        if (!Storage::exists($file_name))
            throw new Exception("file doesn't exist",422);
        return storage_path("app/$file_name");
    }


    public function check_in(array $files, Group $group): array
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
        } catch (Exception $e) {
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


    private function downloadFilesAsZip(array $filePaths, string $zipFileName): string
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

    public function check_out(Request $request , Group $group): array
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

        $lock = Lock::create([
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
        TrackFileChanges::dispatchSync($lock);
        return [
            'files' => new FileResource($file),
            'message' => 'File successfully checked out',
        ];
    }

    public function getAvailableFilesWithVersions($file)
    {

        $versions = $file->locks()->orderBy('Version_number', 'desc')->get();

        $uniqueVersions = $versions->unique('Version_number');
        if ($uniqueVersions->isEmpty()) {
            return [
                'versions' => null,
                'message' => 'No versions found for this file',
            ];
        }

        return [
            'versions' => LockResource::collection($uniqueVersions),
            'message' => 'Available versions for the specified file',
        ];
    }



    public function getAllFiles($request)
    {
        $files = $this->fileRepositry->index($request);
        return [
            "data" => FileResource::collection($files),
            "message" => "all files",
        ];
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


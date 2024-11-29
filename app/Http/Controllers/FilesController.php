<?php

namespace App\Http\Controllers;

use App\Http\Requests\Check_inRequest;
use App\Http\Requests\Check_outRequest;
use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;
use App\Models\File;
use App\Services\FileService;
use App\Http\Responses\Response;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;
class FilesController extends Controller
{

     private FileService $FileService ;

    public function __construct(FileService $FileService)
    {
       $this->FileService = $FileService ;
    }


   /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Group $group)
    {
        if(!$request->user()->isMember($group))
            return response(["message" => "user is not member of this group"],401);
        $files = $group->files()->paginate(10);
        return FileResource::collection($files);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_file(FileRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->store_file($request);
            return Response::Success(new FileResource($data['file']), $data['message']);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
        //  return new  FileResource($file);
    }

    /**
     * Display the specified resource.
     */
    public function show(Group  $group,File $file)
    {

        //return $this->returnSuccessMessage($msg = "success", $errNum = "S000");
        return new  FileResource($file);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileRequest $request, Group $group, File $file)
    {
        $file->update(
            $request->validated()
        );
        return new FileResource($file);
    }

    public function download(Request $request,Group $group,File $file){

        $data = [];
        try {
            $data = $this->FileService->download($request);
            return response()->download($data);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }

    }



    public function check_in(Check_inRequest $request)
    {
        $result = [];
        try {
            $result = $this->FileService->check_in($request->input('files'));

            if (!empty($result['zip_path'])) {
               // return Response::Success([], 'Download will start. Please check your browser.');
                return Response::Success(
                    $result['zip_path'],
                    "File successfully checked in"
                );
            }

            return Response::Success($result['files'], $result['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($result, $message);
        }
    }

    public function check_out(Check_outRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->check_out($request);
            return Response::Success($data['files'], $data['message']);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
        //  return new  FileResource($file);
    }

    public function getAvailableFilesWithVersions($groupId, $fileId): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->getAvailableFilesWithVersions($groupId , $fileId);
            return Response::Success($data['versions'], $data['message']);

        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function getGroupFiles(Group  $group)
    {
        $data =[];
        try {
            $data = $this->FileService->getGroupFiles($group);
            return Response::Success(FileResource::collection($data['files']), $data['message']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    public function getAllFiles()
    {
        $files = $this->FileService->getAllFiles();
        return FileResource::collection($files);
    }

    public function deleteFileWithLocks(File $file): JsonResponse
    {

        try {
            $result = $this->FileService->deleteFileWithLocks($file);
            return Response::Success([], $result['message']);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }

    }

    public function softDeleteFile(File $file): JsonResponse
    {
        try {
            $result = $this->FileService->softDeleteFile($file);
            return Response::Success($result['file'], $result['message']);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }

}

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
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;
class FilesController extends Controller
{

     private FileService $FileService ;

    public function __construct(FileService $FileService)
    {
       $this->FileService = $FileService ;
    }


   /**
     * Display a listing of files inside a specific group.
     */
    public function indexPerGroup(Request $request, Group $group)
    {
        $data =[];
        try {
            $data = $this->FileService->indexPerGroup($request,$group);
            return Response::Success(($data['files']), $data['message'], withPagination:true);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store_file(FileRequest $request, Group $group): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->store_file($request, $group);
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

        return new  FileResource($file);
    }


    public function download(Request $request,Group $group,File $file){

        $data = [];
        try {
            $data = $this->FileService->download($group,$file,$request->version_number);
            return response()->download($data);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message , 422);
        }

    }



    public function check_in(Check_inRequest $request , Group $group)
    {
        $result = [];
        try {
            $result = $this->FileService->check_in($request->input('files'), $group);

            if (!empty($result['zip_path'])) {
               return response()->download($result['zip_path']);
            }

            return Response::Success($result['files'], $result['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($result, $message);
        }
    }

    public function check_out(Check_outRequest $request ,Group $group): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->check_out($request,$group);
            return Response::Success($data['files'], $data['message']);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function getAvailableFilesWithVersions(Group $group,File $file): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->getAvailableFilesWithVersions( $file);
            return Response::Success($data['versions'], $data['message']);

        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }



    public function getAllFiles(Request $request)
    {
        $data = $this->FileService->getAllFiles($request);
        return Response::Success($data["data"],$data["message"],withPagination: true);
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

    public function getOperations(Group $group, File $file): JsonResponse
    {
        try {
            $result = $this->FileService->getFileOperations($file);
            return Response::Success($result['operations'], $result['message'], withPagination: true);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }

    public function getOperationsAsCSV(){

    }

    public function getOperationsAsPDF(){
        
    }

    
}

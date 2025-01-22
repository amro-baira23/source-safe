<?php

namespace App\Http\Controllers;

use App\Exports\FileOperationsExport;
use App\Http\Requests\Check_outRequest;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Http\Requests\FileDownloadRequest;
use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;
use App\Models\File;
use App\Services\FileService;
use App\Http\Responses\Response;
use App\Models\Group;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
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
    public function store(FileRequest $request, Group $group): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->store($request, $group);
            return Response::Success(new FileResource($data['file']), $data['message']);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Group  $group,File $file)
    {

        return new  FileResource($file);
    }


    public function download(FileDownloadRequest $request,Group $group,File $file){

        $data = [];
        try {
            $data = $this->FileService->download($group,$file,$request->version_number);
            return response()->download($data);

        } catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message , 422);
        }

    }



    public function checkIn(CheckInRequest $request , Group $group)
    {
        $result = [];
        try {
            $result = $this->FileService->checkIn($request->input('files'), $group);
            $files_ids = collect($request->input("files"))->pluck("file_id");
            $data_load = File::whereIn("id",$files_ids)->get()->pluck("name")->implode(", ");
            session(["data" => $data_load]);
            if (!empty($result['zip_path'])) {
               return response()->download($result['zip_path']);
            }
            return Response::Success($result['files'], $result['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($result, $message);
        }
    }

    public function checkOut(CheckOutRequest $request ,Group $group): JsonResponse
    {
        $data = [];
        try {
            $data = $this->FileService->checkOut($request);
            session(["data" => File::find($request->file_id)->name]);
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
            return Response::Success($result['operations'], $result['message'], );
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }

    public function getOperationsAsCSV(Group $group, File $file){
        return Excel::download(new FileOperationsExport($file),"$file->name-operations.csv",ExcelExcel::CSV,[
            'Content-Type' => 'text/csv',
      ]);
    }

    public function getOperationsAsPDF(Group $group, File $file){
        return Excel::download(new FileOperationsExport($file),"$file->name-operations.pdf",ExcelExcel::DOMPDF);
    }

    public function indexWithNotActive( Group $group): JsonResponse
    {
        $data = $this->FileService->indexWithNotActive($group);
        return Response::Success($data["data"],$data["message"],withPagination: true);
    }

    public function activate(Group $group,File $file): JsonResponse
    {
        try {
            $result = $this->FileService->activate($file);
            return Response::Success($result['file'], $result['message'], );
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }


}

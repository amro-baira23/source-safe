<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Lock;
use App\Services\FileService;
use Illuminate\Support\Facades\URL;
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
    public function index()
    {
        $files = File::all();
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
            return Response::Success($data['file'], $data['message']);

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
        $file->update([
            "name" => $request->name,
            "type" => $request->type
        ]);
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
        //  ret

    }

}

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
use Illuminate\Http\JsonResponse;
use Throwable;
class FilesController extends Controller
{
     /**
     * Display a listing of the resource.
     */

     private FileService $FileService ;

    public function __construct(FileService $FileService)
    {
       $this->FileService = $FileService ;
    }


    public function index()
    {
        $files = File::all();
        return new  FileResource($files);
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
        try{
            $data = $this->FileService->store_file($request);
            return Response::Success($data['file'],$data['message'] );

        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
        //  return new  FileResource($file);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $files = File::find($id);

        //return $this->returnSuccessMessage($msg = "success", $errNum = "S000");
        return new  FileResource($files);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

}

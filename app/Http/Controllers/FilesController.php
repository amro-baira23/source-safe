<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Lock;
use Illuminate\Support\Facades\URL;

class FilesController extends Controller
{
     /**
     * Display a listing of the resource.
     */
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
    public function store(FileRequest $request)
    {

        $filename = '';
        if($request->hasFile('path')){
            $filename  = $request->file('path')->store('public/files');
        }
        $file = File::query()->create([
        'name'=>$request['name'],
        'path'=>$filename,
        'group_id'=>$request['group_id'],
        'active' => 0,
        ]);

        $filelocks = Lock::query()->create([
            'user_id' => auth()->user()->id,
            'file_id' => $file->id,
            'status' => 0 ,
            'type' => $file->extension(),
            'size' => $file->getsize(),
            'version_number' => 1,
            'date'=> now(),
        ]);

         //return $this->returnSuccessMessage($msg = "success", $errNum = "S000");
         return new  FileResource($file);
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

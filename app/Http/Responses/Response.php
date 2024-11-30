<?php

namespace App\Http\Responses;


use Illuminate\Http\JsonResponse;

class Response{

    public static function Success($data, $message, $code=200, $withPagination = false): JsonResponse
    {
        if ($withPagination){
            return response()->json([
                'status' => 1,
                'data' => $data,
                'message' => $message,
                'current_page' => $data->resource?->currentPage() ,
                'last_page' => $data->resource?->lastPage(),
            ],$code);
        }
            
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => $message,
        ],$code);
    }


    public static function Error($data, $message, $code=500): JsonResponse
    {
        return response()->json([
            'status' => 0,
            'data' => $data,
            'message' => $message
        ],$code);
    }

    public static function Validation($data, $message, $code=422): JsonResponse
    {
        return response()->json([
            'status' => 0,
            'data' => $data,
            'message' => $message
        ],$code);
    }
}

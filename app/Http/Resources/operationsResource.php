<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class operationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "id" => $this->id,
            "user" => $this->user->username ?? null,
            "file" => $this->file->name ?? null,
            "status" => $this->status,
            "type" => $this->type,
            "size"=> $this->size,
            "Version_number"=> $this->Version_number ,
            "created_at" =>$this->created_at->format("Y-m-d h:m"),
            "updated_at" =>$this->updated_at->format("Y-m-d h:m"),
        ];
    }
}

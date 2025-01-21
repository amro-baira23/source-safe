<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'group_id' => $this->group_id,
            'is_locked' => (bool) $this->status,
            'active_status'=> (bool) $this->active,
            "created_at" =>$this->created_at->format("Y-m-d h:m"),
            "updated_at" =>$this->updated_at->format("Y-m-d h:m"),
        ];
    }

    public function getlocks(){
        $res = [];
        if($this->locks){
            foreach($this->locks  as $lock){
                $res[] = [
                'user_id' => $lock->user_id,
                'file_id' => $lock->file_id,
                'status'=>$lock->status,
                'type'=>$lock->type,
                'size'=>$lock->size,
                'version_number'=>$lock->version_number,
                'date'=>$lock->date,
                ];
            }
        }
        return $res;
    }
}

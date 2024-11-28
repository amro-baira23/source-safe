<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
                'username' => $this->username,
                'email' => $this->email,
                'roles' => $this->getroles(),
            ];

    }
    public function getroles(){
        $res = [];
        if($this->roles){
            foreach($this->roles  as $role){
                $res[] = [
                'id' => $role->id,
                'name' => $role->name,
                ];
            }
        }
        return $res;
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupUserRoleResource extends JsonResource
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
        if($this->perms){
            foreach($this->perms  as $perm){
                $res[] = [
                'id' => $perm->id,
                'role' => $perm->role,
                ];
            }
        }
        return $res;
    }

}

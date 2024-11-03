<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            "name" => $this->name,
            "users" =>$this->getUsers()
        ];
    }
    public function getUsers(){
        $res = [];
        if($this->users){
            foreach($this->users  as $user){
                $res[] = [
                'id' => $user->id,
                'name' => $user->username,
                'email'=>$user->email,
                'role'=> $user->pivot->role,
                'approved' => $user->pivot->approved
                ];
            }
        }
        return $res;
    }

}

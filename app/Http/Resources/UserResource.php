<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{   
    public function toArray($request)
    {
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'status' => $this->status,
        'role' => [
            'id' => $this->role?->id,
            'name' => $this->role?->name
        ],
        'mitra' => $this->mitra ? [
            'id'=>$this->mitra->id,
            'name'=>$this->mitra->name,
            'code'=>$this->mitra->code
        ]: null,
        'email_verified_at' => $this->email_verified_At,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
}
}
<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'avatar_url' => empty($this->avatar_url)
                ? null
                : asset("storage/" . $this->avatar_url),
            'identity_provider' => $this->identity_provider,
            'role' => $this->role,
        ];
    }
}

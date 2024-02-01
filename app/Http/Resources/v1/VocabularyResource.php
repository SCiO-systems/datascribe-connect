<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class VocabularyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'listname' => $this->listname,
            'description' => $this->description,
            'isGlobal' => $this->isGlobal,
            'role' => $this->whenPivotLoaded('vocabulary_user', function () {
                return $this->pivot->role;
            }, $this->role($request->user())),
            'body' => $this->body,
        ];
    }
}

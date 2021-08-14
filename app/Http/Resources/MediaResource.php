<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'id'           => $this->id,
            'uuid'         => $this->uuid,
            'name'         => $this->name,
            'file_name'    => $this->file_name,
            'url'          => $this->getUrl(),
            'properties'   => $this->custom_properties,
            'type'         => $this->type,

        ];
    }
}

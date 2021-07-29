<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
        'title' => $this->title,
        'slug' => $this->slug,
        'description' => $this->description,
        'category_id' => $this->category_id,
        'manufacturer_id' => $this->manufacturer_id,
        'price' => $this->price,
        'quantity' => $this->quantity,
        'attributes' => $this->attributes()->toArray(),
        'thumb' => $this->image_url
      ];

    }
}

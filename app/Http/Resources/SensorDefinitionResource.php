<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorDefinitionResource extends JsonResource
{
    /**
     * keep the updated_at and created_at in the correct format.
     */
   public function toArray(Request $request) {
       return [
           ...parent::toArray($request),
           'created_at' => $this->created_at->format('Y-m-d H:i'),
           'updated_at' => $this->updated_at->format('Y-m-d H:i'),
       ];
   }
}

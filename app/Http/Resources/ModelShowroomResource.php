<?php

namespace App\Http\Resources;

use App\Models\ModelShowroom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ModelShowroom */
class ModelShowroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_name' => $this->model_name,
            'year' => $this->year,
            'price' => (float) $this->price,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'shop',
            'attributes' => [
                'name' => $this->name,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'status' => $this->status,
                'maxDeliveryDistance' => $this->max_delivery_distance,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [],
            'includes' => [],
            'links' => [],
        ];
    }
}

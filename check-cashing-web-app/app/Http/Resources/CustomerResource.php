<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'middle_inital' => $this->middle_inital,
            'last_name' => $this->last_name,
            'address_street' => $this->address_street,
            'address_city' => $this->address_city,
            'address_state' => $this->address_state,
            'address_zip' => $this->address_zip,
            'phone_number' => $this->phone_number,
            'dl_number' => $this->dl_number,
            'dl_state' => $this->dl_state,
            'dob' => $this->dob,
            'dl_picture_link' => $this->dl_picture_link,
            'self_picture_link' => $this->self_picture_link,
            'notes' => $this->notes,
            'slug' => $this->slug
        ];
    }
}

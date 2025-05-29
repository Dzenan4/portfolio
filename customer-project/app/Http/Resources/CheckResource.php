<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckResource extends JsonResource
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
            'company_name' => $this->company_name,
            'company_address_street' => $this->company_address_street,
            'company_address_city' => $this->company_address_city,
            'company_address_state' => $this->company_address_state,
            'company_address_zip' => $this->company_address_zip,
            'account_number' => $this->account_number,
            'routing_number' => $this->routing_number,
            'type' => $this->type,
            'cashing_status' => $this->cashing_status,
            'notes' => $this->notes,
            'slug' => $this->slug
        ];
    }
}

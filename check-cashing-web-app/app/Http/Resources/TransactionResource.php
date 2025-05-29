<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'check_id' => $this->check_id,
            'date' => $this->date,
            'check_amount' => $this->amount,
            'payout_amount' => $this->payout_amount,
            'charge_amount' => $this->charge_amount,
            'charge_percentage' => $this->charge_percentage,
            'check_number' => $this->check_number,
            'check_picture_link' => $this->check_picture_link,
            'slug' => $this->slug
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Enums\InquiryStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'event_date' => $this->event_date,
            'message' => $this->message,
            'status' => $this->status instanceof InquiryStatus ? $this->status->value : $this->status,
            'archived' => $this->archived,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

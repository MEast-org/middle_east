<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'conversation_id'=>$this->conversation_id,
            'sender_id'=>$this->sender_id,
            'message'=> $this->message_type != 'text' ? url($this->message) : $this->message,
            'message_type'=> $this->message_type,
            'created_at' => Carbon::parse($this->created_at)->format('g:i A'),
            'is_read'=>$this->is_read,
        ];
    }
}

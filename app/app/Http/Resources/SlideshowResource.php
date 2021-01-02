<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SlideshowResource extends JsonResource
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
            'device' => $this->device,
            'action' => $this->action,
            'next_action' => $this->next_action,
            'queue_title' => $this->queue_title,
            'next_queue_title' => $this->next_queue_title
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IndexState extends JsonResource
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
            'state' => $this->state,
            'total' => $this->total,
            'current' => $this->current,
            'message' => $this->message,
            'retries' => $this->retries,
            'percentage' => $this->getPercentage()
        ];
    }
}

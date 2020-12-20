<?php

namespace App\Http\Resources;

use App\Models\Queue;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $filePath = "{$this->index->getPublicFilePath()}/{$this->index->file_name}";
        return [
            'id' => $this->id,
            'state' => $this->state,
            'file_path' => $filePath
        ];
    }
}

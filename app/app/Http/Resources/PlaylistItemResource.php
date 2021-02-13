<?php

namespace App\Http\Resources;

use App\Utility\PathUtility;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistItemResource extends JsonResource
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
            'path' => PathUtility::getPublicPath($this->path)
        ];
    }
}

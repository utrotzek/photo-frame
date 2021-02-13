<?php

namespace App\Http\Resources;

use App\Aggregator\PlaylistAggregator;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'images' => PlaylistAggregator::getImageCountForPlaylist($this->resource),
            'items' => new PlaylistItemResourceCollection($this->items()->get())
        ];
    }
}

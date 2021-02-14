<?php
namespace App\Aggregator;

use App\Models\Index;
use App\Models\Playlist;
use App\Models\PlaylistItem;

class PlaylistAggregator
{
    public static function getImageCountForPlaylist(Playlist $playlist): int
    {
        $sum = 0;
        /** @var PlaylistItem $playlistItem */
        foreach ($playlist->items()->get() as $playlistItem) {
            $path = $playlistItem->getAttribute('path');
            $sum += Index::query()->where('path', 'like', $path.'%')->count();
        }
        return $sum;
    }
}

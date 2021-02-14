<?php
namespace App\DataHandler;

use App\Models\Playlist;
use App\Models\PlaylistItem;

class PlaylistDataHandler
{
    public function createPlaylist(string $name, array $paths): void
    {
        $playlist = new Playlist(['name' => $name]);
        $playlist->save();
        $this->addPathsToPlaylist($playlist, $paths);
    }

    public function updatePlaylist(Playlist $playlist,string $name, array $paths)
    {
        $playlist['name'] = $name;
        $playlist->save();

        $playlist->items()->delete();
        $this->addPathsToPlaylist($playlist, $paths);
    }
    protected function addPathsToPlaylist(Playlist $playlist, array $paths)
    {
        $items = [];
        foreach ($paths as $path){
            $items[] = new PlaylistItem(['path' => $path]);
        }
        $playlist->items()->saveMany($items);
    }
}

<?php

namespace App\Http\Controllers;

use App\DataHandler\PlaylistDataHandler;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\PlaylistResourceCollection;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlaylistController extends Controller
{
    /**
     * Returns all existing playlists
     */
    public function index(): Response
    {
        return new Response(
            new PlaylistResourceCollection(Playlist::all())
        );
    }

    /**
     * Store a newly created playlist in the storage
     */
    public function store(Request $request, PlaylistDataHandler $dataHandler): Response
    {
        $name = $request->input('name');
        $paths = $request->input('paths');

        if (!$name) {
            return new Response('The playlist must have a name', 400);
        }

        if (!$paths || count($paths) <= 0) {
            return new Response('Paths must have at least one entry', 400);
        }

        $dataHandler->createPlaylist($name, $paths);
        return new Response('Successfully saved');
    }

    /**
     * Updates a playlist in the storage
     */
    public function update(Request $request, PlaylistDataHandler $dataHandler, Playlist $playlist): Response
    {
        $name = $request->input('name');
        $paths = $request->input('paths');

        if (!$name) {
            return new Response('The playlist must have a name', 400);
        }

        if (!$paths || count($paths) <= 0) {
            return new Response('Paths must have at least one entry', 400);
        }

        $dataHandler->updatePlaylist($playlist, $name, $paths);
        return new Response('Successfully saved');
    }

    /**
     * Remove the specified playlist from the storage.
     */
    public function destroy(Playlist $playlist): Response
    {
        $name = $playlist['name'];
        $playlist->delete();
        return new Response(sprintf('Playlist %1$s successfully deleted.', $name));
    }
}

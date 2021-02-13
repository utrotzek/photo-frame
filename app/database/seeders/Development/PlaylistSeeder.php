<?php


namespace Database\Seeders\Development;


use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Database\Seeder;

class PlaylistSeeder extends Seeder
{

    public function run(){
        $urlaube = new Playlist(['name' => 'animals']);
        $urlaube->save();
        $urlaube->items()->saveMany([
            new PlaylistItem(['path' => '/images/2019/animals']),
            new PlaylistItem(['path' => '/images/2020/animals'])
        ]);
    }
}

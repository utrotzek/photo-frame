<?php

namespace Database\Seeders;

use Database\Seeders\Development\PlaylistSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
           PlaylistSeeder::class
        ]);
    }
}

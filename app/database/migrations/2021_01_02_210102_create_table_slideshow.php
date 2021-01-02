<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTableSlideshow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slideshow', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('action')->default('stop');
            $table->string('next_action')->nullable();
            $table->string('queue_title')->nullable();
            $table->string('next_queue_title')->nullable();
            $table->string('device')->nullable();
        });

        DB::table('slideshow')->insert(
            [
                'action' => \App\Models\Slideshow::ACTION_STOP,
                'device' => 'main'
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slideshow');
    }
}

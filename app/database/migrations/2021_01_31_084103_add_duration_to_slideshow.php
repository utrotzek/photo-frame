<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationToSlideshow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slideshow', function (Blueprint $table) {
            $table->integer('duration')->default(30);
            $table->integer('next_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slideshow', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('next_duration');
        });
    }
}

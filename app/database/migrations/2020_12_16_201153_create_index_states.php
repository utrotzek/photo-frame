<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('index_states', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('state')->default('waiting');
            $table->integer('total')->default(0);
            $table->integer('current')->default(0);
            $table->string('message')->default('');
            $table->integer('retries')->default(0);
            $table->dateTime('last_run')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('index_states');
    }
}

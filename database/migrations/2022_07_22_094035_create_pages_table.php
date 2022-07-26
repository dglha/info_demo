<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('keyword')->nullable();
            $table->string('image')->nullable();
            $table->string('url');
            $table->integer('traffic_per_day');
            $table->integer('traffic_sum');
            $table->integer('traffic_remain');
            $table->integer('onsite');
            $table->tinyInteger('status')->default(0);
            $table->string('note')->nullable();
            $table->time('timeout')->default('02:00:00');
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
        Schema::dropIfExists('pages');
    }
}

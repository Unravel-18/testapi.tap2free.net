<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
			$table->string('server')->unique();
			$table->string('name')->nullable();
			$table->string('country')->nullable();
            $table->string('img_flag')->nullable();
            $table->string('img_map')->nullable();
			$table->string('ip')->nullable();
			$table->enum('type', ['free', 'pro'])->default('free');
            $table->text('ca_crt')->nullable();
            $table->text('client1_crt')->nullable();
            $table->text('client1_key')->nullable();
            $table->timestamp('date')->nullable();
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
        Schema::drop('servers');
    }
}

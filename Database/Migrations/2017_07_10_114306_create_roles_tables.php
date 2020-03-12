<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug')->unique();
            $table->string('label')->nullable();
            $table->text('description');
            $table->boolean('is_editable')
                ->default(true);
            $table->unsignedBigInteger('module_id')
                ->nullable()
                ->default(null);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('creator_id')
                ->nullable()
                ->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}

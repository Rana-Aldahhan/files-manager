<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('reserver_id')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->string('name');
            $table->string('path');
            $table->enum('status',['checkedIn','free'])->default('free');
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
        Schema::dropIfExists('files');
    }
};

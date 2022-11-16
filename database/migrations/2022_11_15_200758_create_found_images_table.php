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
        Schema::create('found_images', function (Blueprint $table) {
            $table->id();
            $table->integer('blog_id');
            $table->string('page_url');
            $table->string('image_url');
            $table->boolean('original_exists');
            $table->boolean('success');
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
        Schema::dropIfExists('found_images');
    }
};

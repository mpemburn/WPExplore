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
        Schema::create('blogs_list', function (Blueprint $table) {
            $table->id();
            $table->string('site');
            $table->integer('blog_id');
            $table->string('blog_url', 500);
            $table->string('last_updated');
            $table->string('admin_email');
            $table->string('current_theme');
            $table->string('template');
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
        Schema::dropIfExists('blogs_list');
    }
};

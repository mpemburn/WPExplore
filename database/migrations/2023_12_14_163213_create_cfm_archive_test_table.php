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
        Schema::create('cfm_archive_test', function (Blueprint $table) {
            $table->id();
            $table->string('server', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('web_root', 255)->nullable();
            $table->string('index_url', 255)->nullable();
            $table->string('page_title', 255)->nullable();
            $table->string('redirect_url', 255)->nullable();
            $table->integer('error_code')->default(0);
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
        Schema::dropIfExists('cfm_archive_test');
    }
};

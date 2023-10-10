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
        Schema::create('code_scans', function (Blueprint $table) {
            $table->id();
            $table->string('key_word', 30)->nullable();
            $table->string('filename', 500)->nullable();
            $table->bigInteger('line_num');
            $table->string('line_contents', 500)->nullable();
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
        Schema::dropIfExists('code_scans');
    }
};

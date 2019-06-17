<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsAndCommentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('news_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('news_id');
            $table->string('comment');
            $table->string('user_name');
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
        Schema::dropIfExists('news');
        Schema::dropIfExists('news_comments');
    }
}

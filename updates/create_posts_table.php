<?php namespace Vortechron\Point\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostsTable extends Migration
{

    public function up()
    {
        Schema::create('vortechron_point_posts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->string('title')->nullable();
            $table->string('slug')->index();
            $table->string('type')->nullable();
            $table->integer('point')->default(1);
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_html')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vortechron_point_posts');
    }

}

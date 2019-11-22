<?php namespace Vortechron\Point\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCustomersTable extends Migration
{

    public function up()
    {
        Schema::create('vortechron_point_customers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('phone')->index();
            $table->string('email')->index();
            $table->string('ic')->index();
            $table->timestamps();
        });
        Schema::create('vortechron_point_customer_points', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('post_id');
            $table->integer('point')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vortechron_point_customers');
        Schema::dropIfExists('vortechron_point_customer_points');
    }

}

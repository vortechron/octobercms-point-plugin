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
            $table->string('id')->primary();
            $table->string('name')->nullable();
            $table->string('phone')->index();
            $table->string('email')->index();
            $table->timestamps();
        });
        Schema::create('vortechron_point_customer_points', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increment('id');
            $table->unsignedInteger('customer_id');
            $table->string('type')->nullable();
            $table->string('type_id')->index();
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

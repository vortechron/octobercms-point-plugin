<?php namespace Vortechron\Point\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class SupportApi extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function ($table) {
            $table->string('api_token', 80)->after('password')
                                ->unique()
                                ->nullable()
                                ->default(null);
        });
    }

    public function down()
    {
        Schema::table('backend_users', function ($table) {
            $table->dropColumn(['api_token']);
        });
    }

}

<?php namespace Vortechron\Point\Updates;

use October\Rain\Database\Updates\Migration;
use DbDongle;

class UpdateTimestampsNullable extends Migration
{
    public function up()
    {
        DbDongle::disableStrictMode();

        DbDongle::convertTimestamps('vortechron_point_posts');
        DbDongle::convertTimestamps('vortechron_point_categories');
    }

    public function down()
    {
        // ...
    }
}

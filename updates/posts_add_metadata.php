<?php namespace Vortechron\Point\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Vortechron\Point\Models\Category as CategoryModel;

class PostsAddMetadata extends Migration
{

    public function up()
    {
        if (Schema::hasColumn('vortechron_point_posts', 'metadata')) {
            return;
        }

        Schema::table('vortechron_point_posts', function($table)
        {
            $table->mediumText('metadata')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('vortechron_point_posts', 'metadata')) {
            Schema::table('vortechron_point_posts', function ($table) {
                $table->dropColumn('metadata');
            });
        }
    }

}

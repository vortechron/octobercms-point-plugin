<?php namespace Vortechron\Point\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Vortechron\Point\Models\Category as CategoryModel;

class CategoriesAddNestedFields extends Migration
{

    public function up()
    {
        if (Schema::hasColumn('vortechron_point_categories', 'parent_id')) {
            return;
        }

        Schema::table('vortechron_point_categories', function($table)
        {
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
        });

        foreach (CategoryModel::all() as $category) {
            $category->setDefaultLeftAndRight();
            $category->save();
        }
    }

    public function down()
    {
    }

}
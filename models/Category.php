<?php namespace Vortechron\Point\Models;

use Str;
use Model;
use Url;
use Vortechron\Point\Models\Post;
use October\Rain\Router\Helper as RouterHelper;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;

    public $table = 'vortechron_point_categories';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /*
     * Validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|between:3,64|unique:vortechron_point_categories',
        'code' => 'nullable|unique:vortechron_point_categories',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'description',
        ['slug', 'index' => true]
    ];

    protected $guarded = [];

    public $belongsToMany = [
        'posts' => ['Vortechron\Point\Models\Post',
            'table' => 'vortechron_point_posts_categories',
            'order' => 'published_at desc',
            'scope' => 'isPublished'
        ],
        'posts_count' => ['Vortechron\Point\Models\Post',
            'table' => 'vortechron_point_posts_categories',
            'scope' => 'isPublished',
            'count' => true
        ]
    ];
}

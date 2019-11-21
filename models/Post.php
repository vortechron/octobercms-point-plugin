<?php namespace Vortechron\Point\Models;

use Db;
use Url;
use App;
use Str;
use Html;
use Lang;
use Model;
use Markdown;
use BackendAuth;
use ValidationException;
use Backend\Models\User;
use Carbon\Carbon;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Vortechron\Point\Classes\TagProcessor;

/**
 * Class Post
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'vortechron_point_posts';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /*
     * Validation
     */
    public $rules = [
        'title'   => 'required',
        'slug'    => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:vortechron_point_posts'],
        'content' => 'required',
        'excerpt' => ''
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'content',
        'content_html',
        'excerpt',
        'metadata',
        ['slug', 'index' => true]
    ];

    /**
     * @var array Attributes to be stored as JSON
     */
    protected $jsonable = ['metadata'];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * The attributes on which the post list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'         => 'vortechron.point::lang.sorting.title_asc',
        'title desc'        => 'vortechron.point::lang.sorting.title_desc',
        'created_at asc'    => 'vortechron.point::lang.sorting.created_asc',
        'created_at desc'   => 'vortechron.point::lang.sorting.created_desc',
        'updated_at asc'    => 'vortechron.point::lang.sorting.updated_asc',
        'updated_at desc'   => 'vortechron.point::lang.sorting.updated_desc',
        'published_at asc'  => 'vortechron.point::lang.sorting.published_asc',
        'published_at desc' => 'vortechron.point::lang.sorting.published_desc',
        'random'            => 'vortechron.point::lang.sorting.random'
    ];

    /*
     * Relations
     */
    public $belongsTo = [
        'user' => ['Backend\Models\User']
    ];

    public $belongsToMany = [
        'categories' => [
            'Vortechron\Point\Models\Category',
            'table' => 'vortechron_point_posts_categories',
            'order' => 'name'
        ]
    ];

    public $attachMany = [
        'featured_images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public function getRecordUrlAttribute()
    {
        $type = 'event';
        $id = $this->id;

        return Url::to("record-point/{$type}/{$id}");
    }

    public function beforeSave()
    {
        $this->type = 'event';
    }
}

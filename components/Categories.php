<?php namespace Vortechron\Point\Components;

use Db;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Vortechron\Point\Models\Category as EventCategory;

class Categories extends ComponentBase
{
    /**
     * @var Collection A collection of categories to display
     */
    public $categories;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    /**
     * @var string Reference to the current category slug.
     */
    public $currentCategorySlug;

    public function componentDetails()
    {
        return [
            'name'        => 'vortechron.point::lang.settings.category_title',
            'description' => 'vortechron.point::lang.settings.category_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'vortechron.point::lang.settings.category_slug',
                'description' => 'vortechron.point::lang.settings.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'displayEmpty' => [
                'title'       => 'vortechron.point::lang.settings.category_display_empty',
                'description' => 'vortechron.point::lang.settings.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
            'categoryPage' => [
                'title'       => 'vortechron.point::lang.settings.category_page',
                'description' => 'vortechron.point::lang.settings.category_page_description',
                'type'        => 'dropdown',
                'default'     => 'event/category',
                'group'       => 'vortechron.point::lang.settings.group_links',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    /**
     * Load all categories or, depending on the <displayEmpty> option, only those that have event posts
     * @return mixed
     */
    protected function loadCategories()
    {
        $categories = EventCategory::with('posts_count')->getNested();
        if (!$this->property('displayEmpty')) {
            $iterator = function ($categories) use (&$iterator) {
                return $categories->reject(function ($category) use (&$iterator) {
                    if ($category->getNestedPostCount() == 0) {
                        return true;
                    }
                    if ($category->children) {
                        $category->children = $iterator($category->children);
                    }
                    return false;
                });
            };
            $categories = $iterator($categories);
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    protected function linkCategories($categories)
    {
        $eventPostsComponent = $this->getComponent('eventPosts', $this->categoryPage);

        return $categories->each(function ($category) use ($eventPostsComponent) {
            $category->setUrl(
                $this->categoryPage,
                $this->controller,
                [
                    'slug' => $this->urlProperty($eventPostsComponent, 'categoryFilter')
                ]
            );

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}

<?php namespace Vortechron\Point\Components;

use Lang;
use Redirect;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use October\Rain\Database\Model;
use October\Rain\Database\Collection;
use Vortechron\Point\Models\Post as EventPost;
use Vortechron\Point\Models\Category as EventCategory;
use Vortechron\Point\Models\Settings as EventSettings;

class Posts extends ComponentBase
{
    /**
     * A collection of posts to display
     *
     * @var Collection
     */
    public $posts;

    /**
     * Parameter to use for the page number
     *
     * @var string
     */
    public $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use
     *
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages
     *
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts
     *
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to categories
     *
     * @var string
     */
    public $categoryPage;

    /**
     * If the post list should be ordered by another attribute
     *
     * @var string
     */
    public $sortOrder;

    public function componentDetails()
    {
        return [
            'name'        => 'vortechron.point::lang.settings.posts_title',
            'description' => 'vortechron.point::lang.settings.posts_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'vortechron.point::lang.settings.posts_pagination',
                'description' => 'vortechron.point::lang.settings.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'vortechron.point::lang.settings.posts_filter',
                'description' => 'vortechron.point::lang.settings.posts_filter_description',
                'type'        => 'string',
                'default'     => '',
            ],
            'postsPerPage' => [
                'title'             => 'vortechron.point::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'vortechron.point::lang.settings.posts_per_page_validation',
                'default'           => '10',
            ],
            'noPostsMessage' => [
                'title'             => 'vortechron.point::lang.settings.posts_no_posts',
                'description'       => 'vortechron.point::lang.settings.posts_no_posts_description',
                'type'              => 'string',
                'default'           => Lang::get('vortechron.point::lang.settings.posts_no_posts_default'),
                'showExternalParam' => false,
            ],
            'sortOrder' => [
                'title'       => 'vortechron.point::lang.settings.posts_order',
                'description' => 'vortechron.point::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc',
            ],
            'categoryPage' => [
                'title'       => 'vortechron.point::lang.settings.posts_category',
                'description' => 'vortechron.point::lang.settings.posts_category_description',
                'type'        => 'dropdown',
                'default'     => 'event/category',
                'group'       => 'vortechron.point::lang.settings.group_links',
            ],
            'postPage' => [
                'title'       => 'vortechron.point::lang.settings.posts_post',
                'description' => 'vortechron.point::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'event/post',
                'group'       => 'vortechron.point::lang.settings.group_links',
            ],
            'exceptPost' => [
                'title'             => 'vortechron.point::lang.settings.posts_except_post',
                'description'       => 'vortechron.point::lang.settings.posts_except_post_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'vortechron.point::lang.settings.posts_except_post_validation',
                'default'           => '',
                'group'             => 'vortechron.point::lang.settings.group_exceptions',
            ],
            'exceptCategories' => [
                'title'             => 'vortechron.point::lang.settings.posts_except_categories',
                'description'       => 'vortechron.point::lang.settings.posts_except_categories_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'vortechron.point::lang.settings.posts_except_categories_validation',
                'default'           => '',
                'group'             => 'vortechron.point::lang.settings.group_exceptions',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        $options = EventPost::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function listPosts()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $isPublished = !$this->checkEditor();

        $posts = EventPost::with('categories')->listFrontEnd([
            'page'             => $this->property('pageNumber'),
            'sort'             => $this->property('sortOrder'),
            'perPage'          => $this->property('postsPerPage'),
            'search'           => trim(input('search')),
            'category'         => $category,
            'published'        => $isPublished,
            'exceptPost'       => is_array($this->property('exceptPost'))
                ? $this->property('exceptPost')
                : preg_split('/,\s*/', $this->property('exceptPost'), -1, PREG_SPLIT_NO_EMPTY),
            'exceptCategories' => is_array($this->property('exceptCategories'))
                ? $this->property('exceptCategories')
                : preg_split('/,\s*/', $this->property('exceptCategories'), -1, PREG_SPLIT_NO_EMPTY),
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $eventPostComponent = $this->getComponent('eventPost', $this->postPage);
        $eventPostsComponent = $this->getComponent('eventPosts', $this->categoryPage);

        $posts->each(function ($post) use ($eventPostComponent, $eventPostsComponent) {
            $post->setUrl(
                $this->postPage,
                $this->controller,
                [
                    'slug' => $this->urlProperty($eventPostComponent, 'slug')
                ]
            );

            $post->categories->each(function ($category) use ($eventPostsComponent) {
                $category->setUrl(
                    $this->categoryPage,
                    $this->controller,
                    [
                        'slug' => $this->urlProperty($eventPostsComponent, 'categoryFilter')
                    ]
                );
            });
        });

        return $posts;
    }

    protected function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) {
            return null;
        }

        $category = new EventCategory;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    protected function checkEditor()
    {
        $backendUser = BackendAuth::getUser();

        return $backendUser && $backendUser->hasAccess('vortechron.point.access_posts') && EventSettings::get('show_all_posts', true);
    }
}

<?php namespace Vortechron\Point\Components;

use Cms\Classes\ComponentBase;
use Lang;
use Response;
use Cms\Classes\Page;
use Vortechron\Point\Models\Post as EventPost;
use Vortechron\Point\Models\Category as EventCategory;

class RssFeed extends ComponentBase
{
    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

    /**
     * Reference to the page name for the main event page.
     * @var string
     */
    public $eventPage;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    public function componentDetails()
    {
        return [
            'name'        => 'vortechron.point::lang.settings.rssfeed_title',
            'description' => 'vortechron.point::lang.settings.rssfeed_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'categoryFilter' => [
                'title'       => 'vortechron.point::lang.settings.posts_filter',
                'description' => 'vortechron.point::lang.settings.posts_filter_description',
                'type'        => 'string',
                'default'     => '',
            ],
            'sortOrder' => [
                'title'       => 'vortechron.point::lang.settings.posts_order',
                'description' => 'vortechron.point::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'created_at desc',
            ],
            'postsPerPage' => [
                'title'             => 'vortechron.point::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'vortechron.point::lang.settings.posts_per_page_validation',
                'default'           => '10',
            ],
            'eventPage' => [
                'title'       => 'vortechron.point::lang.settings.rssfeed_event',
                'description' => 'vortechron.point::lang.settings.rssfeed_event_description',
                'type'        => 'dropdown',
                'default'     => 'event/post',
                'group'       => 'vortechron.point::lang.settings.group_links',
            ],
            'postPage' => [
                'title'       => 'vortechron.point::lang.settings.posts_post',
                'description' => 'vortechron.point::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'event/post',
                'group'       => 'vortechron.point::lang.settings.group_links',
            ],
        ];
    }

    public function getEventPageOptions()
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

        $xmlFeed = $this->renderPartial('@default');

        return Response::make($xmlFeed, '200')->header('Content-Type', 'text/xml');
    }

    protected function prepareVars()
    {
        $this->eventPage = $this->page['eventPage'] = $this->property('eventPage');
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

        $this->page['link'] = $this->pageUrl($this->eventPage);
        $this->page['rssLink'] = $this->currentPageUrl();
    }

    protected function listPosts()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = EventPost::with('categories')->listFrontEnd([
            'sort'     => $this->property('sortOrder'),
            'perPage'  => $this->property('postsPerPage'),
            'category' => $category
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $eventPostComponent = $this->getComponent('eventPost', $this->postPage);

        $posts->each(function ($post) use ($eventPostComponent) {
            $post->setUrl($this->postPage, $this->controller, [
                'slug' => $this->urlProperty($eventPostComponent, 'slug')
            ]);
        });

        return $posts;
    }

    protected function loadCategory()
    {
        if (!$categoryId = $this->property('categoryFilter')) {
            return null;
        }

        if (!$category = EventCategory::whereSlug($categoryId)->first()) {
            return null;
        }

        return $category;
    }
}

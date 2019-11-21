<?php namespace Vortechron\Point\Components;

use Event;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Vortechron\Point\Models\Post as EventPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Post extends ComponentBase
{
    /**
     * @var EventPost The post model used for display.
     */
    public $post;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    public function componentDetails()
    {
        return [
            'name'        => 'vortechron.point::lang.settings.post_title',
            'description' => 'vortechron.point::lang.settings.post_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'vortechron.point::lang.settings.post_slug',
                'description' => 'vortechron.point::lang.settings.post_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'categoryPage' => [
                'title'       => 'vortechron.point::lang.settings.post_category',
                'description' => 'vortechron.point::lang.settings.post_category_description',
                'type'        => 'dropdown',
                'default'     => 'event/category',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            foreach ($params as $paramName => $paramValue) {
                $records = EventPost::transWhere($paramName, $paramValue, $oldLocale)->first();

                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams[$paramName] = $records[$paramName];
                }
            }
            return $newParams;
        });
    }

    public function onRun()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->post = $this->page['post'] = $this->loadPost();
    }

    public function onRender()
    {
        if (empty($this->post)) {
            $this->post = $this->page['post'] = $this->loadPost();
        }
    }

    protected function loadPost()
    {
        $slug = $this->property('slug');

        $post = new EventPost;

        $post = $post->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $post->transWhere('slug', $slug)
            : $post->where('slug', $slug);

        if (!$this->checkEditor()) {
            $post = $post->isPublished();
        }

        try {
            $post = $post->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if ($post && $post->categories->count()) {
            $eventPostsComponent = $this->getComponent('eventPosts', $this->categoryPage);

            $post->categories->each(function ($category) use ($eventPostsComponent) {
                $category->setUrl($this->categoryPage, $this->controller, [
                    'slug' => $this->urlProperty($eventPostsComponent, 'categoryFilter')
                ]);
            });
        }

        return $post;
    }

    public function previousPost()
    {
        return $this->getPostSibling(-1);
    }

    public function nextPost()
    {
        return $this->getPostSibling(1);
    }

    protected function getPostSibling($direction = 1)
    {
        if (!$this->post) {
            return;
        }

        $method = $direction === -1 ? 'previousPost' : 'nextPost';

        if (!$post = $this->post->$method()) {
            return;
        }

        $postPage = $this->getPage()->getBaseFileName();

        $eventPostComponent = $this->getComponent('eventPost', $postPage);
        $eventPostsComponent = $this->getComponent('eventPosts', $this->categoryPage);

        $post->setUrl($postPage, $this->controller, [
            'slug' => $this->urlProperty($eventPostComponent, 'slug')
        ]);

        $post->categories->each(function ($category) use ($eventPostsComponent) {
            $category->setUrl($this->categoryPage, $this->controller, [
                'slug' => $this->urlProperty($eventPostsComponent, 'categoryFilter')
            ]);
        });

        return $post;
    }

    protected function checkEditor()
    {
        $backendUser = BackendAuth::getUser();

        return $backendUser && $backendUser->hasAccess('vortechron.point.access_posts');
    }
}

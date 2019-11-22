<?php namespace Vortechron\Point;

use Event;
use Backend;
use Controller;
use Backend\Controllers\Users;
use System\Classes\PluginBase;
use Vortechron\Point\Models\Post;
use Illuminate\Support\Facades\Route;
use Vortechron\Point\Models\Category;
use Vortechron\Point\Classes\TagProcessor;

class Plugin extends PluginBase
{
    public $require = ['Mohsin.Rest'];

    public function boot()
    {
        Users::extendFormFields(function ($form, $model, $context) {
            $form->addTabFields([
                'api_token' => [
                    'label'   => 'Api Token',
                    'comment' => 'Api token key for api request',
                    'type' => 'text',
                    'tab' => 'Api'
                ],
            ]);
        });
    }
    public function pluginDetails()
    {
        return [
            'name'        => 'vortechron.point::lang.plugin.name',
            'description' => 'vortechron.point::lang.plugin.description',
            'author'      => 'Amirul Adli',
            'icon'        => 'icon-pencil',
            'homepage'    => 'https://github.com/vortechron/point-plugin'
        ];
    }

    public function registerComponents()
    {
        return [
            'Vortechron\Point\Components\RecordPoint'       => 'recordPoint',
            'Vortechron\Point\Components\Post'       => 'post',
            'Vortechron\Point\Components\Posts'      => 'posts',
            'Vortechron\Point\Components\Categories' => 'categories',
            'Vortechron\Point\Components\RssFeed'    => 'rssFeed'
        ];
    }

    public function registerPermissions()
    {
        return [
            'vortechron.point.manage_settings' => [
                'tab'   => 'vortechron.point::lang.event.tab',
                'label' => 'vortechron.point::lang.event.manage_settings'
            ],
            'vortechron.point.access_posts' => [
                'tab'   => 'vortechron.point::lang.event.tab',
                'label' => 'vortechron.point::lang.event.access_posts'
            ],
            'vortechron.point.access_categories' => [
                'tab'   => 'vortechron.point::lang.event.tab',
                'label' => 'vortechron.point::lang.event.access_categories'
            ],
            'vortechron.point.access_other_posts' => [
                'tab'   => 'vortechron.point::lang.event.tab',
                'label' => 'vortechron.point::lang.event.access_other_posts'
            ],
            'vortechron.point.access_publish' => [
                'tab'   => 'vortechron.point::lang.event.tab',
                'label' => 'vortechron.point::lang.event.access_publish'
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'event' => [
                'label'       => 'vortechron.point::lang.event.menu_label',
                'url'         => Backend::url('vortechron/point/posts'),
                'icon'        => 'icon-bullhorn',
                'permissions' => ['vortechron.point.*'],
                'order'       => 300,

                'sideMenu' => [
                    'new_post' => [
                        'label'       => 'vortechron.point::lang.posts.new_post',
                        'icon'        => 'icon-plus',
                        'url'         => Backend::url('vortechron/point/posts/create'),
                        'permissions' => ['vortechron.point.access_posts']
                    ],
                    'posts' => [
                        'label'       => 'vortechron.point::lang.event.posts',
                        'icon'        => 'icon-copy',
                        'url'         => Backend::url('vortechron/point/posts'),
                        'permissions' => ['vortechron.point.access_posts']
                    ],
                    'categories' => [
                        'label'       => 'vortechron.point::lang.event.categories',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('vortechron/point/categories'),
                        'permissions' => ['vortechron.point.access_categories']
                    ]
                ]
            ],
            'customer' => [
                'label'       => 'Customers',
                'url'         => Backend::url('vortechron/point/customers'),
                'icon'        => 'icon-hand-peace-o',
                'permissions' => ['vortechron.point.*'],
                'order'       => 300,
            ],
        ];
    }

    public function registerNodes()
    {
        return [
            'record-point/{slug}' => [
                'controller' => 'Vortechron\Point\Http\RecordPointController@record',
                'action'     => 'store'
            ],
        ];
    }
}

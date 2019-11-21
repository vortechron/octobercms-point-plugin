<?php namespace Vortechron\Point\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Backend\Classes\Controller;
use Vortechron\Point\Models\Post;

class Posts extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['vortechron.point.access_other_posts', 'vortechron.point.access_posts'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Vortechron.Point', 'event', 'posts');
    }

    public function create()
    {
        BackendMenu::setContextSideMenu('new_post');

        $this->bodyClass = 'compact-container';
        $this->addCss('/plugins/vortechron/point/assets/css/vortechron.point-preview.css');
        $this->addJs('/plugins/vortechron/point/assets/js/post-form.js');

        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container';
        $this->addCss('/plugins/vortechron/point/assets/css/vortechron.point-preview.css');
        $this->addJs('/plugins/vortechron/point/assets/js/post-form.js');

        return $this->asExtension('FormController')->update($recordId);
    }
}

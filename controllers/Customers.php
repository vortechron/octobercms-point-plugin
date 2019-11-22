<?php namespace Vortechron\Point\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Vortechron\Point\Models\Post;
use Vortechron\Point\Widgets\RandomPick;

class Customers extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['vortechron.point.access_other_posts', 'vortechron.point.access_posts'];

    public function __construct()
    {
        parent::__construct();

        $randomPick = new RandomPick($this);
        $randomPick->bindToController();

        BackendMenu::setContext('Vortechron.Point', 'customer', 'customers');
    }
}

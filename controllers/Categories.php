<?php namespace Vortechron\Point\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Backend\Classes\Controller;
use Vortechron\Point\Models\Category;

class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['vortechron.point.access_categories'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Vortechron.Point', 'event', 'categories');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $categoryId) {
                if ((!$category = Category::find($categoryId))) {
                    continue;
                }

                $category->delete();
            }

            Flash::success(Lang::get('vortechron.point::lang.category.delete_success'));
        }

        return $this->listRefresh();
    }
}

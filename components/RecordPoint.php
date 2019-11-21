<?php namespace Vortechron\Point\Components;

use Cms\Classes\ComponentBase;
use Vortechron\Point\Models\Post;
use Illuminate\Support\Facades\App;
use October\Rain\Exception\ApplicationException;
use Vortechron\Point\Models\Customer;

class RecordPoint extends ComponentBase
{
    public $model;

    public function componentDetails()
    {
        return [
            'name'        => 'Record Point',
            'description' => 'Record customer entry and give points based on event type.'
        ];
    }

    public function onRun()
    {
        $this->model = Post::where('type', $this->param('type'))->where('id', $this->param('id'))->first();

        if (! $this->model)
            return \Response::make($this->controller->run('404'), 404);

        if ($this->model->is_expired)
            return \Response::make($this->controller->run('404'), 404);
    }

    public function onContinue()
    {
        $this->page['id'] = $id = post('id');
        $type = $this->param('type');
        $typeId = $this->param('id');

        $customer = Customer::find($id);

        if ($customer) $this->recordPoint($customer, $type, $typeId);

        $this->page['isRegistered'] = $customer ? true : false;
    }

    public function onFinish()
    {
        $type = $this->param('type');
        $typeId = $this->param('id');
        $customer = Customer::create(request()->all());

        $this->recordPoint($customer, $type, $typeId);

        $this->page['isRegistered'] = true;
    }

    public function recordPoint($customer, $type, $id)
    {
        $point = $customer->points()
                    ->where('type', $type)
                    ->where('type_id', $id)
                    ->first();

        if ($point) return;

        $customer->points()->create([
            'type' => $type,
            'type_id' => $id,
            'point' => $point->point
        ]);
    }

    function onRefreshTime()
    {
        return [
            '#finish' => $this->renderPartial('recordPoint::infoform')
        ];
    }
}

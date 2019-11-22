<?php namespace Vortechron\Point\Components;

use Cms\Classes\ComponentBase;
use Vortechron\Point\Models\Post;
use Illuminate\Support\Facades\App;
use Vortechron\Point\Models\Customer;
use October\Rain\Exception\ValidationException;
use October\Rain\Exception\ApplicationException;

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
        $this->model = $this->getModel();

        if (! $this->model)
            return \Response::make($this->controller->run('404'), 404);

        if (! $this->model->is_published)
            return \Response::make($this->controller->run('404'), 404);
    }

    public function getModel()
    {
        return Post::where('slug', $this->param('slug'))->first();
    }

    public function onContinue()
    {
        $this->page['ic'] = $ic = post('ic');

        $customer = Customer::whereIc($ic)->first();

        if ($customer) $this->recordPoint($customer, $this->getModel());

        $this->page['isRegistered'] = $customer ? true : false;
    }

    public function createCustomer()
    {
        $validator = validator(request()->all(), [
            'name' => 'required',
            'phone' => 'required|unique:vortechron_point_customers,phone',
            'ic' => 'required|unique:vortechron_point_customers,ic',
            'email' => 'required|unique:vortechron_point_customers,email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Customer::create(request()->all());
    }

    public function onFinish()
    {
        $customer = $this->createCustomer();

        $this->recordPoint($customer, $this->getModel());

        $this->page['isRegistered'] = true;
    }

    public function recordPoint($customer, Post $post)
    {
        $point = $customer->points()
                    ->where('post_id', $post->id)
                    ->first();

        if ($point) return;

        $customer->points()->create([
            'post_id' => $post->id,
            'point' => $post->point
        ]);
    }

    function onRefreshTime()
    {
        return [
            '#finish' => $this->renderPartial('recordPoint::infoform')
        ];
    }
}

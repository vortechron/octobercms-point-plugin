<?php

namespace Vortechron\Point\Widgets;

use Backend\Classes\WidgetBase;
use Vortechron\Point\Models\Customer;
use Vortechron\Point\Models\CustomerPoint;
use Vortechron\Point\Models\Post;

class RandomPick extends WidgetBase
{
    protected $defaultAlias = 'randomPick';

    public function render()
    {
        $this->vars['posts'] = Post::where('type', 'event')->get();

        return $this->makePartial('default');
    }

    public function onPick()
    {
        $post = post('post');

        if ($post == 'NULL') {
            $points = CustomerPoint::all();

            $user = $this->getRandomResult($points);
        } else {
            $points = CustomerPoint::where('post_id', $post)->get();
        
            $user = $this->getRandomResult($points);
        }

        $this->vars['user'] = $user;
    }

    protected function getRandomResult($points)
    {
        $possibilities = [];

        if ($points->isEmpty()) return null;

        $points->each(function ($point) use (&$possibilities) {
            for ($i = 1; $i <= $point->point; $i++) {
                $possibilities[] = $point->customer_id;
            }
        });

        shuffle($possibilities);
        $result = array_rand($possibilities);

        return Customer::find($possibilities[$result]);
    }

    public function onRefreshTime()
    {
        return [
            '#results' => $this->makePartial('results')
        ];
    }
}
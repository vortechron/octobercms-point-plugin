<?php namespace Vortechron\Point\Http;

use Illuminate\Http\Request;
use Backend\Classes\Controller;
use Vortechron\Point\Components\RecordPoint;
use Vortechron\Point\Models\Customer;
use Vortechron\Point\Models\Post;

/**
 * Record Point Controller Back-end Controller
 */
class RecordPointController extends Controller
{
    public function record(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $component = new RecordPoint();
        $customer = Customer::whereIc($request->ic)->first();

        if (! $customer) $customer = $component->createCustomer();
        
        $component->recordPoint($customer, $post);

        return response()->json([
            'status' => 'success'
        ]);
    }
    
}

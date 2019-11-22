<?php namespace Vortechron\Point\Models;

use Model;

/**
 * Class Post
 */
class CustomerPoint extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'vortechron_point_customer_points';

    public $guarded = [];

    public $belongsTo = [
        'post' => [Post::class]
    ];
    
    /*
     * Validation
     */
    public $rules = [
    ];
}

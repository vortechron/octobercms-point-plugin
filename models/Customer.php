<?php namespace Vortechron\Point\Models;

use Model;

/**
 * Class Post
 */
class Customer extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'vortechron_point_customers';

    public $guarded = [];

    /*
     * Validation
     */
    public $rules = [
    ];

    public $hasMany = [
        'points' => [CustomerPoint::class],
    ];

    public function getTotalPointsAttribute()
    {
        return $this->points->sum('point');
    }
}

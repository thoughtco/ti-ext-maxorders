<?php

namespace Thoughtco\Maxorders\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use Admin\Traits\Locationable;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;

class Timeslots extends Model
{
    use Validation;
    use Locationable;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_maxorders';

    public $timestamps = TRUE;
    
    const LOCATIONABLE_RELATION = 'locations';

    public $casts = [
        'timeslot_day' => 'array',
        'timeslot_categories' => 'array',
    ];
    
    public $relation = [
        'morphToMany' => [
            'locations' => ['Admin\Models\Locations_model', 'name' => 'locationable'],
        ],
    ];
    
    public $rules = [
        'timeslot_label' => 'required',
        'timeslot_max' => 'required|int|min:0',
        'timeslot_start' => 'required|valid_time',
        'timeslot_end' => 'required|valid_time',
    ];
    
    public static function getTimeslotCategoriesOptions()
    {
	    return Categories_model::all()->pluck('name', 'category_id');
    }
    
    public static function getTimeslotDayOptions()
    {
        $days = [];
        for ($i=0; $i<7; $i++)
        {
            $days[$i] = date('l', strtotime('Sunday +'.$i.' days'));
        }
        return $days;
    }
    
    public static function getTimeslotMaxTypeOptions()
    {
        return [
            'orders' => lang('thoughtco.maxorders::default.option_orders'),
            'covers' => lang('thoughtco.maxorders::default.option_covers'),
        ];
    }
    
}
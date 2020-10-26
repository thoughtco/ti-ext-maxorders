<?php

namespace Thoughtco\Maxorders\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;

class Timeslots extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_maxorders';

    public $timestamps = TRUE;

    public $casts = [
        'location_id' => 'integer',
        'timeslot_day' => 'array',
        'timeslot_categories' => 'array',
    ];
    
    public $relation = [
        'belongsTo' => [
            'location' => 'Admin\Models\Locations_model',
        ]
    ];
    
    public $rules = [
        'location_id' => 'required|int',
        'location_day' => 'required',
        'timeslot_max' => 'required|int|min:0',
        'timeslot_start' => 'required|valid_time',
        'timeslot_end' => 'required|valid_time',
        'timeslot_categories' => 'sometimes|required',
    ];
    
    public static function getTimeslotCategoriesOptions()
    {
	    return Categories_model::all()->pluck('name', 'category_id');
    }
    
    public static function getLocationIdOptions()
    {
	    $locations = [];
	    foreach (Locations_model::all() as $location){
	    	$locations[$location->location_id] = $location->location_name;
	    };	  
	    return $locations;  
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
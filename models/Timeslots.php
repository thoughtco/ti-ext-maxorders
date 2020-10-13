<?php

namespace Thoughtco\Maxorders\Models;

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
    ];
    
    public $relation = [
        'belongsTo' => [
            'location' => 'Admin\Models\Locations_model',
        ]
    ];
    
    public $rules = [
        'location_id' => 'required|int',
        'timeslot_max' => 'required|int',
        'timeslot_start' => 'required|valid_time',
        'timeslot_end' => 'required|valid_time',
    ];
    
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
}
<?php 

namespace Thoughtco\Maxorders;

use Admin\Widgets\Form;
use Carbon\Carbon;
use Event;
use Igniter\Local\Classes\Location;
use Illuminate\Foundation\AliasLoader;
use System\Classes\BaseExtension;
use Thoughtco\Maxorders\Listeners\MaxOrders;

class Extension extends BaseExtension
{
    public function boot()
    {
        Event::subscribe(MaxOrders::class);   
    }
    
    public function registerPermissions()
    {
        return [
            'Thoughtco.Maxorders.Manage' => [
                'description' => 'Create, modify and manage maximum orders per timeslot',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'restaurant' => [
                'child' => [
                    'maxorders' => [
                        'priority' => 100,
                        'class' => 'maxorders',
                        'href' => admin_url('thoughtco/maxorders/timeslots'),
                        'title' => lang('thoughtco.maxorders::default.text_title'),
                        'permission' => 'Thoughtco.Maxorders.*',
                    ],
                ],
            ],
        ];
    }  

}

?>
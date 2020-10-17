<?php 

namespace Thoughtco\Maxorders;

use Event;
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
                'description' => 'Create, modify and manage maximum orders allowed per timeslot',
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
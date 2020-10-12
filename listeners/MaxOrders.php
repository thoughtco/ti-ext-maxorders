<?php

namespace Thoughtco\Maxorders\Listeners;

use Admin\Models\Orders_model;
use Carbon\Carbon;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\Local\Facades\Location as LocationFacade;
use Illuminate\Contracts\Events\Dispatcher;
use Thoughtco\Maxorders\Models\Timeslots;

class MaxOrders
{
    use EventEmitter;

    protected static $ordersCache = [];

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotValid', __CLASS__.'@timeslotValid');
    }

    public function timeslotValid($workingSchedule, $timeslot)
    {
        // Skip if the working schedule is not for delivery or pickup
        if ($workingSchedule->getType() == AbstractLocation::OPENING)
            return;

        $dateString = Carbon::parse($timeslot)->toDateString();

        $ordersOnThisDay = $this->getOrders($dateString);

        $locationModel = LocationFacade::current();

        $dayOfWeek = $timeslot->format('w');
        $startTime = Carbon::parse($timeslot);
        $endTime = Carbon::parse($timeslot)->addMinutes($locationModel->getOrderTimeInterval($workingSchedule->getType()));
        
        $maxCount = $locationModel->getOption('limit_orders_count');
        
        Timeslots::where([
            ['location_id', $locationModel->location_id],
            ['timeslot_status', 1],            
        ])
        ->each(function($limitation) use ($dayOfWeek, $startTime, &$maxCount){
            if (in_array($dayOfWeek, $limitation->timeslot_day))
            {
                $limitationStart = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$limitation->timeslot_start);
                $limitationEnd = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$limitation->timeslot_end);
                if ($startTime->between($limitationStart, $limitationEnd))
                {
                    $maxCount = $limitation->timeslot_max;      
                }
            }
        });

        $orderCount = $ordersOnThisDay->filter(function ($order) use ($startTime, $endTime) {
            $orderTime = Carbon::createFromFormat('Y-m-d H:i:s', $order->order_date->format('Y-m-d').' '.$order->order_time);

            return $orderTime->between(
                $startTime,
                $endTime
            );
        });

        if ($orderCount->count() >= $maxCount)
            return FALSE;

    }

    protected function getOrders($date)
    {
        if (array_has(self::$ordersCache, $date))
            return self::$ordersCache[$date];

        $result = Orders_model::where('order_date', $date)
            ->where('location_id', LocationFacade::getId())
            ->whereIn('status_id', array_merge(setting('processing_order_status', []), setting('completed_order_status', [])))
            ->select(['order_time', 'order_date'])
            ->pluck('order_time', 'order_date');

        return self::$ordersCache[$date] = $result;
    }
}

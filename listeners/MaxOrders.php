<?php

namespace Thoughtco\Maxorders\Listeners;

use Admin\Models\Menus_model;
use Admin\Models\Orders_model;
use App;
use Carbon\Carbon;
use Cart;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\Local\Facades\Location as LocationFacade;
use Illuminate\Contracts\Events\Dispatcher;
use Session;
use Thoughtco\Maxorders\Models\Timeslots;

class MaxOrders
{
    use EventEmitter;

    protected static $ordersCache = [];
    protected static $menusCache = [];

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotValid', __CLASS__.'@timeslotValid');
        $dispatcher->listen('igniter.checkout.beforeSaveOrder', __CLASS__.'@beforeSaveOrder');
        $dispatcher->listen('cart.added', __CLASS__.'@cartValidate');
        $dispatcher->listen('cart.updated', __CLASS__.'@cartValidate');
    }

    public function beforeSaveOrder($order, $data)
    {
        $orderDateTime = LocationFacade::instance()->orderDateTime();
        if ($this->checkTimeslot($order->order_type, $orderDateTime, true) === false)
            throw new \ApplicationException(lang('thoughtco.maxorders::default.error_maximum_reached'));
    }

    public function cartValidate($cartItem)
    {
        if (!Session::get('local_info.order-timeslot'))
            return;

        $locationManager = LocationFacade::instance();

        $workingScheduleType = $locationManager->orderType();
        $timeslot = $locationManager->orderDateTime();

        $additionalOrders = 1;
        $coverMenuItems = [];
        foreach (Cart::content() as $cartItem)
        {
            $coverMenuItems[] = (object)[
                'menu_id' => $cartItem->id,
                'quantity' => $cartItem->qty,
            ];
        }

        if ($this->checkTimeslot($workingScheduleType, $timeslot, false, $additionalOrders, $coverMenuItems) === FALSE)
            throw new \ApplicationException(lang('thoughtco.maxorders::default.error_covers_quantity'));
    }

    public function timeslotValid($workingSchedule, $timeslot)
    {
        // Skip if the working schedule is not for delivery or pickup
        if ($workingSchedule->getType() == AbstractLocation::OPENING)
            return;

        return $this->checkTimeslot($workingSchedule->getType(), $timeslot);
    }

    private function checkTimeslot($workingScheduleType, $timeslot, $checkLocationSetting = false, $addAdditionalOrders = 1, $coverMenuItems = [])
    {
        $dateString = Carbon::parse($timeslot)->toDateString();

        $ordersOnThisDay = $this->getOrders($dateString);

        $locationModel = LocationFacade::current();

        $dayOfWeek = $timeslot->format('w');
        $startTime = Carbon::parse($timeslot);
        $endTime = Carbon::parse($timeslot)->addMinutes($locationModel->getOrderTimeInterval($workingScheduleType))->subMinute();

        $removeSlot = false;

        // filter orders to only include the timeslot we need
        $timeslotOrders = $ordersOnThisDay->filter(function ($order) use ($startTime, $endTime) {
            $orderTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$order->order_time);
            return $orderTime->between(
                $startTime,
                $endTime
            );
        });

        // if checking from beforeSaveOrder we also need to be sure we check the location default
        if ($checkLocationSetting && $locationModel->getOption('limit_orders'))
        {
            if ($timeslotOrders->count() >= $locationModel->getOption('limit_orders_count'))
                return FALSE;
        }

        $customerLocation = $locationModel->location_id;

        // get and loop over the extension limitations
        Timeslots::where([
            ['timeslot_status', 1],
        ])
        ->each(function($limitation) use ($customerLocation, &$removeSlot, $dayOfWeek, $startTime, $timeslotOrders, $coverMenuItems, $addAdditionalOrders) {

            if ($limitation->locations AND !$limitation->locations->has($customerLocation))
                return;

            $limitationOrderType = $limitation->timeslot_order_type;

            if (in_array($dayOfWeek, $limitation->timeslot_day))
            {
                $limitationStart = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$limitation->timeslot_start);
                $limitationEnd = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$limitation->timeslot_end);
                if ($startTime->between($limitationStart, $limitationEnd))
                {
                    // order type
                    if (count($limitation->timeslot_order_type) > 0)
                    {
                        // if its not the same as this limitation order type
                        if (!in_array(App::make('location')->orderType(), $limitationOrderType))
                            return;

                        // we only want orders of this order type
                        $timeslotOrders = $timeslotOrders->filter(function($order) use ($limitationOrderType) {
                            return in_array($order->order_type, $limitationOrderType);
                        });
                    }

                    // if limiting by categories then we need to count up the number of items
                    // in the categories
                    if ($limitation->timeslot_max_type == 'covers')
                    {
                        $timeslotOrders = $timeslotOrders->map(function($order) use ($limitation) {
                            $myCount = 0;
                            $order->menus
                            ->each(function($orderMenu) use ($limitation, &$myCount) {
                                if ($this->getMenuCategories($orderMenu->menu_id)->intersect($limitation->timeslot_categories)->count())
                                    $myCount += $orderMenu->quantity;
                            });
                            return $myCount;
                        });

                        $addAdditionalCovers = 0;
                        collect($coverMenuItems)
                            ->each(function($orderMenu) use ($limitation, &$addAdditionalCovers) {
                                if ($this->getMenuCategories($orderMenu->menu_id)->intersect($limitation->timeslot_categories)->count())
                                    $addAdditionalCovers += $orderMenu->quantity;
                            });

                        // get sum of all covers
                        $orderCount = $timeslotOrders->sum() + $addAdditionalCovers;

                    // otherwise we count orders on this day
                    } else {
                        $orderCount = $timeslotOrders->count() + $addAdditionalOrders;
                    }

                    if ($orderCount >= $limitation->timeslot_max)
                        $removeSlot = true;
                }
            }
        });

        if ($removeSlot)
            return FALSE;
    }

    protected function getOrders($date)
    {
        if (array_has(self::$ordersCache, $date))
            return self::$ordersCache[$date];

        $result = Orders_model::where('location_id', LocationFacade::getId())
            ->where('order_date', $date)
            ->whereIn('status_id', array_merge([setting('default_order_status', -1)], setting('processing_order_status', []), setting('completed_order_status', [])))
            ->get()
            ->map(function($order){
                return (object)[
                    'order_type' => $order->order_type,
                    'order_time' => $order->order_time,
                    'menus' => $order->getOrderMenus(),
                ];
            });

        return self::$ordersCache[$date] = $result;
    }

    protected function getMenuCategories($menuId)
    {
        if (array_has(self::$menusCache, $menuId))
            return self::$menusCache[$menuId];

        $result = Menus_model::where('menu_id', $menuId)
            ->first()
            ->categories()
            ->pluck('categories.category_id');

        return self::$menusCache[$menuId] = $result;
    }

}

<?php

namespace Thoughtco\Maxorders\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderTypesField extends Migration
{
    public function up()
    {
        if (Schema::hasTable('thoughtco_maxorders'))
        {
            Schema::table('thoughtco_maxorders', function (Blueprint $table) {
                $table->text('timeslot_order_type')->change();
            });
        }
    }
}

<?php 

namespace Thoughtco\Maxorders\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaxordersTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('thoughtco_maxorders'))
        {
            Schema::create('thoughtco_maxorders', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->text('timeslot_label');
                $table->text('timeslot_locations');
                $table->text('timeslot_day');
                $table->integer('timeslot_order_type');
                $table->time('timeslot_start');
                $table->time('timeslot_end');
                $table->integer('timeslot_max');
                $table->string('timeslot_max_type')->default('orders');
                $table->text('timeslot_categories');
                $table->boolean('timeslot_status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('thoughtco_maxorders');
    }
}
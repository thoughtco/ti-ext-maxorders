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
                $table->integer('location_id');
                $table->text('timeslot_day');
                $table->time('timeslot_start');
                $table->time('timeslot_end');
                $table->integer('timeslot_max');
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
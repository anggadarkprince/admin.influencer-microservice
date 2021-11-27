<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->string('code')->after('user_id');
            $table->string('influencer_email')->after('email');
            $table->string('address')->after('influencer_email')->nullable();
            $table->string('address2')->after('address')->nullable();
            $table->string('city')->after('address2')->nullable();
            $table->string('country')->after('city')->nullable();
            $table->string('zip')->after('country')->nullable();
            $table->tinyInteger('complete')->after('zip')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('user_id');
            $table->dropColumn('influencer_email');
            $table->dropColumn('address');
            $table->dropColumn('address2');
            $table->dropColumn('city');
            $table->dropColumn('country');
            $table->dropColumn('zip');
            $table->dropColumn('complete');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNormalizationStatusAndInfosFieldsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('normalization_status')->after('staff_note')->default(false);
            $table->string('nim_code')->nullable();
            $table->string('normalization_code')->nullable();
            $table->string('normalization_counters')->nullable();
            $table->string('normalization_date')->nullable();
            $table->string('qr_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['normalization_date', 'normalization_counters', 'nim_code', 'normalization_status', 'normalization_code', 'qr_code']);
        });
    }
}

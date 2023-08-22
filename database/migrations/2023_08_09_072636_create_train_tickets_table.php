<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('train_tickets', function (Blueprint $table) {
            $table->id()->foreign('receipt_train.train_ticket_id');
            $table->string('start');
            $table->string('end');
            $table->string('passenger');
            $table->string('class');
            $table->date('schedule');
            $table->bigInteger('user_id');
            $table->string('status')->default('disponible');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_ticket');
    }
};

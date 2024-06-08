<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('patient_id')->constrained('users')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('cancelled')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('events')) {
            Schema::dropIfExists('events');
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign('events_user_id_foreign');
            });
        }
    }
};

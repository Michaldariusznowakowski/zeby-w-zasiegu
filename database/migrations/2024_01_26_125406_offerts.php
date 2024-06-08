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
        Schema::create('offers', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->foreignId('doctor_id')->index();
            $table->boolean('active')->default(false);
            $table->string('description')->default('');
            $table->string('image')->default('default/profile.png');
            $table->string('address')->default('');
            $table->decimal('longitude', 9, 6)->default(0.0);
            $table->decimal('latitude', 9, 6)->default(0.0);
            $table->json('working_hours')->default(json_encode([
                'monday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => false], // 'ignore' is used to mark a day as 'closed
                'tuesday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => false],
                'wednesday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => false],
                'thursday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => false],
                'friday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => false],
                'saturday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => true],
                'sunday' => ['start' => '08:00', 'end' => '16:00', 'ignore' => true],
            ]));
            $table->integer('default_appointment_duration')->default(45);
            $table->timestamps();
            $table->foreign('doctor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('offers')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
            Schema::dropIfExists('offers');
        }
    }
};

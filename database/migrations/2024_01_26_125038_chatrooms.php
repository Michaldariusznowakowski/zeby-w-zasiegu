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
        Schema::create('chatrooms', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->foreignId('dentist_id')->index();
            $table->foreignId('patient_id')->index();
            $table->timestamps();
            $table->boolean('dentist_has_unread_messages')->default(false);
            $table->boolean('patient_has_unread_messages')->default(false);
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('dentist_id')->references('id')->on('users');
            $table->boolean('sent_email_to_patient')->default(false);
            $table->boolean('sent_email_to_dentist')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chatrooms')) {
            Schema::table('chatrooms', function (Blueprint $table) {
                $table->dropForeign('chatrooms_patient_id_foreign');
                $table->dropForeign('chatrooms_dentist_id_foreign');
            });
            Schema::dropIfExists('chatrooms');
        }
    }
};

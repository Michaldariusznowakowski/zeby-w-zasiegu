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
        Schema::create('messages', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->string('message');
            $table->string('iv');
            $table->string('ek');
            $table->string('ekl');
            $table->foreignId('sender_id');
            $table->foreignId('recipient_id');
            $table->boolean('recipient_lost_key')->default(false);
            $table->boolean('sender_lost_key')->default(false);
            $table->boolean('recipient_read')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            $table->integer('type');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->foreignId('chatroom_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign('messages_sender_id_foreign');
                $table->dropForeign('messages_receiver_id_foreign');
                $table->dropForeign('messages_chatroom_id_foreign');
            });
            Schema::dropIfExists('messages');
        }
    }
};

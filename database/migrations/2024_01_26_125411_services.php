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
        Schema::create('services', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->foreignId('offer_id')->index();
            $table->string('name')->default('');
            $table->string('description')->default('');
            $table->integer('minprice')->default(0);
            $table->integer('maxprice')->default(0);
            $table->timestamps();
            $table->foreign('offer_id')->references('id')->on('offers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign('services_offer_id_foreign');
            });
            Schema::dropIfExists('services');
        }
    }
};

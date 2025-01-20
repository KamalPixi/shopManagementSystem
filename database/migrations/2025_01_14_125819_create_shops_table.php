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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->decimal('gst', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->json('currency'); // ['code' => 'MYR', 'symbol' => 'RM']
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};

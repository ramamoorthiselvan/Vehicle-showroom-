<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_showrooms', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 100);
            $table->unsignedSmallInteger('year');
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);          // 1 = active, 0 = inactive
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            // the homepage query filters on status and sorts by display_order
            $table->index(['status', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_showrooms');
    }
};

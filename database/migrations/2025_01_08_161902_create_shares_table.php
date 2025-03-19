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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            // renamed to user_id in  later migration
            $table->foreignId('user_id')->constrained();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->float('amount', 2);
            $table->boolean('sent');
            $table->boolean('seen');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};

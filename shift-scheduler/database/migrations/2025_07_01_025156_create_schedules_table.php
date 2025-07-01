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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->enum('shift_type', ['A', 'B']); // A: 6AM-6PM, B: 6PM-6AM
            $table->enum('status', ['on_duty', 'off_duty'])->default('on_duty');
            $table->timestamps();
            
            // Ensure unique person-date combination
            $table->unique(['person_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

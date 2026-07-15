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
        Schema::create('donor_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('donor_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // A donor can only respond once per request
            $table->unique(['blood_request_id', 'donor_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_responses');
    }
};

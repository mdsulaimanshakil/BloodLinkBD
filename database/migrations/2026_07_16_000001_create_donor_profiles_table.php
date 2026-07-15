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
        Schema::create('donor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->string('district', 100);
            $table->string('phone', 20)->unique();
            $table->date('last_donation_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('donation_count')->default(0);
            $table->decimal('trust_score', 3, 2)->default(0.00);
            $table->timestamps();

            // Indexes for fast lookups
            $table->index('blood_group');
            $table->index('district');
            $table->index(['blood_group', 'district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_profiles');
    }
};

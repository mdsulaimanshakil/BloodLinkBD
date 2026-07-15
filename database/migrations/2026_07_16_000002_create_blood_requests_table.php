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
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->string('district', 100);
            $table->string('hospital');
            $table->enum('urgency', ['normal', 'urgent', 'critical'])->default('normal');
            $table->enum('status', ['active', 'fulfilled', 'expired', 'removed'])->default('active');
            $table->string('requester_phone', 20);
            $table->foreignId('requester_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('additional_notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for fast lookups
            $table->index('blood_group');
            $table->index('district');
            $table->index(['blood_group', 'district']);
            $table->index('status');
            $table->index('urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};

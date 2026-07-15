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
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 100); // e.g. 'verify_donor', 'remove_request', 'reject_donor'
            $table->string('target_type', 100); // e.g. 'DonorProfile', 'BloodRequest'
            $table->unsignedBigInteger('target_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('admin_id');
            $table->index(['target_type', 'target_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};

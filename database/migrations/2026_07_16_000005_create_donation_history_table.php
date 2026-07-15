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
        Schema::create('donation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blood_request_id')->nullable()->constrained('blood_requests')->onDelete('set null');
            $table->date('donated_at');
            $table->string('hospital')->nullable();
            $table->string('district', 100)->nullable();
            $table->tinyInteger('rating')->nullable()->comment('1-5 rating given by requester');
            $table->text('feedback_notes')->nullable();
            $table->timestamps();

            $table->index('donor_id');
            $table->index('donated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_history');
    }
};

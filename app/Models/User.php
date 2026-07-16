<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ──────────────────────────────────────────
    // Role helpers
    // ──────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDonor(): bool
    {
        return $this->role === 'donor';
    }

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * A user (donor) has one donor profile.
     */
    public function donorProfile(): HasOne
    {
        return $this->hasOne(DonorProfile::class);
    }

    /**
     * A user (donor) has many donation history records.
     */
    public function donationHistory(): HasMany
    {
        return $this->hasMany(DonationHistory::class, 'donor_id');
    }

    /**
     * A user may have posted blood requests (as a logged-in requester).
     */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class, 'requester_id');
    }

    /**
     * A user (donor) has many donor responses.
     */
    public function donorResponses(): HasMany
    {
        return $this->hasMany(DonorResponse::class, 'donor_id');
    }

    /**
     * Admin audit logs authored by this admin user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_id');
    }
}

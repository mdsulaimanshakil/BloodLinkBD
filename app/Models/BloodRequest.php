<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class BloodRequest extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     *
     * Automatically sets expires_at based on urgency when creating a request.
     * Critical = 48h, Urgent = 4 days, Normal = 7 days.
     */
    protected static function booted(): void
    {
        static::creating(function (BloodRequest $request) {
            if (is_null($request->expires_at) && $request->urgency) {
                $hours = self::EXPIRY_HOURS[$request->urgency] ?? self::EXPIRY_HOURS['normal'];
                $request->expires_at = Carbon::now()->addHours($hours);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_name',
        'blood_group',
        'district',
        'hospital',
        'urgency',
        'status',
        'requester_phone',
        'requester_id',
        'additional_notes',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Urgency priority map — used for sorting (lower = higher priority).
     */
    public const URGENCY_PRIORITY = [
        'critical' => 1,
        'urgent'   => 2,
        'normal'   => 3,
    ];

    /**
     * Expiry hours per urgency level.
     */
    public const EXPIRY_HOURS = [
        'critical' => 48,       // 2 days
        'urgent'   => 96,       // 4 days
        'normal'   => 168,      // 7 days
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * The user who posted this request (optional — public requests have no account).
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * All donor responses to this request.
     */
    public function donorResponses(): HasMany
    {
        return $this->hasMany(DonorResponse::class);
    }

    /**
     * Donation history entries linked to this request.
     */
    public function donationHistory(): HasMany
    {
        return $this->hasMany(DonationHistory::class);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    /**
     * Scope: only active (non-expired, non-removed) requests.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: sort by urgency priority then most recent.
     */
    public function scopeByUrgencyThenRecent(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(urgency, 'critical', 'urgent', 'normal')")
                     ->orderByDesc('created_at');
    }

    /**
     * Scope: filter by district.
     */
    public function scopeDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    /**
     * Scope: filter by blood group.
     */
    public function scopeBloodGroup(Builder $query, string $bloodGroup): Builder
    {
        return $query->where('blood_group', $bloodGroup);
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Human-readable urgency label.
     */
    public function getUrgencyLabelAttribute(): string
    {
        return ucfirst($this->urgency);
    }

    /**
     * Masked requester phone — last 3 digits only, for public display.
     */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = $this->requester_phone;
        $len   = strlen($phone);

        if ($len <= 3) {
            return $phone;
        }

        return substr($phone, 0, $len - 6) . '***' . substr($phone, -3);
    }

    /**
     * Whether this request has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

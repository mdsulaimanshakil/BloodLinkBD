<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DonorProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'blood_group',
        'district',
        'phone',
        'last_donation_date',
        'is_available',
        'is_verified',
        'donation_count',
        'trust_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_donation_date' => 'date',
        'is_available'       => 'boolean',
        'is_verified'        => 'boolean',
        'donation_count'     => 'integer',
        'trust_score'        => 'decimal:2',
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * The user that owns this donor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Donation history records for this donor.
     */
    public function donationHistory(): HasMany
    {
        return $this->hasMany(DonationHistory::class, 'donor_id', 'user_id');
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    /**
     * Scope: eligible donors — verified, available, and 90+ days since last donation (or never donated).
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query
            ->where('is_available', true)
            ->where('is_verified', true)
            ->where(function (Builder $q) {
                $q->whereNull('last_donation_date')
                  ->orWhere('last_donation_date', '<=', Carbon::now()->subDays(90)->toDateString());
            });
    }

    /**
     * Scope: filter by blood group.
     */
    public function scopeBloodGroup(Builder $query, string $bloodGroup): Builder
    {
        return $query->where('blood_group', $bloodGroup);
    }

    /**
     * Scope: filter by district.
     */
    public function scopeDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Whether this donor has a "Trusted Donor" badge (3+ donations).
     */
    public function getIsTrustedAttribute(): bool
    {
        return $this->donation_count >= 3;
    }

    /**
     * Masked phone number — shows only the last 3 digits.
     * e.g. "01712345678" → "01712***678"
     */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = $this->phone;
        $len   = strlen($phone);

        if ($len <= 3) {
            return $phone;
        }

        return substr($phone, 0, $len - 6) . '***' . substr($phone, -3);
    }

    /**
     * Days since last donation (null if never donated).
     */
    public function getDaysSinceLastDonationAttribute(): ?int
    {
        if (! $this->last_donation_date) {
            return null;
        }

        return (int) $this->last_donation_date->diffInDays(Carbon::now());
    }
}

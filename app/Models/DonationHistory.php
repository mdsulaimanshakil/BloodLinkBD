<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'donation_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'donor_id',
        'blood_request_id',
        'donated_at',
        'hospital',
        'district',
        'rating',
        'feedback_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'donated_at' => 'date',
        'rating'     => 'integer',
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * The donor (user) who made this donation.
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    /**
     * The blood request this donation was in response to (nullable).
     */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Star rating display (e.g. "★★★★☆" for rating 4).
     */
    public function getStarRatingAttribute(): string
    {
        if (! $this->rating) {
            return 'Not rated';
        }

        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}

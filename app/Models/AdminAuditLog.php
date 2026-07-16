<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminAuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'notes',
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * The admin user who performed this action.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'verify_donor'    => 'Verified Donor',
            'reject_donor'    => 'Rejected Donor',
            'remove_request'  => 'Removed Request',
            'restore_request' => 'Restored Request',
            default           => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Short model name for the target (e.g., "DonorProfile" → "Donor Profile").
     */
    public function getTargetLabelAttribute(): string
    {
        return ucwords(preg_replace('/(?<!^)([A-Z])/', ' $1', class_basename($this->target_type)));
    }
}

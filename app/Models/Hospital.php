<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'district',
        'address',
        'contact',
        'type',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    /**
     * Scope: filter by district.
     */
    public function scopeDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    /**
     * Scope: only blood banks.
     */
    public function scopeBloodBanks(Builder $query): Builder
    {
        return $query->where('type', 'blood_bank');
    }

    /**
     * Scope: only hospitals.
     */
    public function scopeHospitals(Builder $query): Builder
    {
        return $query->where('type', 'hospital');
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Whether this hospital has map coordinates.
     */
    public function getHasCoordinatesAttribute(): bool
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }

    /**
     * Human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'blood_bank' ? 'Blood Bank' : 'Hospital';
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FingerprintDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fingerprint_devices';

    protected $fillable = [
        'name',
        'serial_number',
        'ip_address',
        'is_online',
        'last_sync',
        'consecutive_failures',
        'last_error_message',
        'last_error_at',
        'location'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_sync' => 'datetime',
        'last_error_at' => 'datetime'
    ];

    // Scopes
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeWithErrors($query)
    {
        return $query->where('consecutive_failures', '>', 0);
    }

    public function scopeRecentlyActive($query, $minutes = 5)
    {
        return $query->where('last_sync', '>=', Carbon::now()->subMinutes($minutes));
    }

    // Helper Methods
    public function markAsOnline()
    {
        $this->update([
            'is_online' => true,
            'last_sync' => now()
        ]);
    }

    public function markAsOffline()
    {
        $this->update([
            'is_online' => false
        ]);
    }

    public function recordError($message)
    {
        $this->update([
            'last_error_at' => now(),
            'last_error_message' => $message,
            'consecutive_failures' => $this->consecutive_failures + 1
        ]);
    }

    public function resetErrors()
    {
        $this->update([
            'consecutive_failures' => 0,
            'last_error_message' => null
        ]);
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_online) {
            return $this->consecutive_failures > 0 ? 'warning' : 'online';
        }
        return 'offline';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'online' => 'success',
            'warning' => 'warning',
            'offline' => 'danger',
            default => 'secondary'
        };
    }

    public function getLastSyncHumanAttribute(): string
    {
        return $this->last_sync ? $this->last_sync->diffForHumans() : 'Never';
    }
}

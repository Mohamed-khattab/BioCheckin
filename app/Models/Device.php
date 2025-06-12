<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'serial_number',
        'ip_address',
        'is_online',
        'last_sync',
        'firmware_version',
        'location',
        'total_records_today',
        'total_records_week',
        'total_records_month',
        'last_error_at',
        'last_error_message',
        'consecutive_failures',
        'performance_metrics'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_sync' => 'datetime',
        'last_error_at' => 'datetime',
        'performance_metrics' => 'json',
        'total_records_today' => 'integer',
        'total_records_week' => 'integer',
        'total_records_month' => 'integer',
        'consecutive_failures' => 'integer'
    ];

    // Relationships
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

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

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('last_error_at', '>=', now()->subDays(7));
    }

    // Accessors
    public function getStatusAttribute()
    {
        if ($this->is_online) {
            return $this->last_error_at && $this->last_error_at->gt(now()->subDays(7))
                ? 'maintenance'
                : 'online';
        }
        return 'offline';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'online' => 'success',
            'maintenance' => 'warning',
            'offline' => 'danger',
            default => 'secondary'
        };
    }

    public function getLastSyncHumanAttribute()
    {
        return $this->last_sync ? $this->last_sync->diffForHumans() : 'Never';
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
            'last_error_at' => now()
        ]);
        
        // Log the error
        \Log::error("Device {$this->name} error: {$message}");
    }

    public function resetErrors()
    {
        $this->consecutive_failures = 0;
        $this->last_error_message = null;
        $this->save();
    }

    public function updateRecordCounts()
    {
        $this->update([
            'total_records_today' => $this->attendanceRecords()->whereDate('check_in', today())->count(),
            'total_records_week' => $this->attendanceRecords()->whereBetween('check_in', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_records_month' => $this->attendanceRecords()->whereMonth('check_in', now()->month)->whereYear('check_in', now()->year)->count()
        ]);
    }

    public function updateMetrics()
    {
        $now = Carbon::now();
        
        $this->total_records_today = $this->attendanceRecords()
            ->whereDate('created_at', $now->toDateString())
            ->count();
            
        $this->total_records_week = $this->attendanceRecords()
            ->whereBetween('created_at', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek()
            ])->count();
            
        $this->total_records_month = $this->attendanceRecords()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();
            
        $this->save();
    }

    protected static function booted()
    {
        static::creating(function ($device) {
            if (!isset($device->performance_metrics)) {
                $device->performance_metrics = [
                    'uptime_percentage' => 100,
                    'average_response_time' => 0,
                    'success_rate' => 100
                ];
            }
        });
    }
} 
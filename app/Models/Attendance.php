<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'device_id',
        'check_in',
        'check_out',
        'status',
        'notes',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'total_hours',
        'working_hours',
        'is_late',
        'is_early_departure'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'working_hours' => 'decimal:2',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('attendance_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('attendance_date', Carbon::now()->month)
                    ->whereYear('attendance_date', Carbon::now()->year);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeLate($query, $threshold = '09:00:00')
    {
        return $query->whereTime('check_in_time', '>', $threshold);
    }

    // Accessors & Mutators
    public function getIsLateAttribute()
    {
        if (!$this->check_in_time) return false;
        return Carbon::parse($this->check_in_time)->format('H:i:s') > '09:00:00';
    }

    public function getIsOverworkingAttribute()
    {
        return $this->total_hours > 8;
    }

    public function getWorkStatusAttribute()
    {
        if (!$this->check_in) return 'Not checked in';
        if (!$this->check_out) return 'Working';
        return 'Completed';
    }

    // Helper Methods
    public function checkOut()
    {
        if (!$this->check_out) {
            $this->check_out = now();
            $this->check_out_time = now()->toTimeString();
            $this->calculateTotalHours();
            $this->save();
        }
    }

    public function calculateTotalHours()
    {
        if ($this->check_in && $this->check_out) {
            $this->total_hours = $this->check_in->diffInSeconds($this->check_out) / 3600;
        }
    }

    public function calculateWorkingHours(): float
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $hours = $this->check_out->diffInMinutes($this->check_in) / 60;
        return round($hours, 2);
    }

    public function updateWorkingHours(): void
    {
        $this->working_hours = $this->calculateWorkingHours();
        $this->save();
    }

    public function checkLateStatus(): void
    {
        if (!$this->check_in || !$this->employee?->activeSchedule) {
            return;
        }

        $expectedCheckIn = $this->employee->getExpectedCheckInTime($this->check_in);
        if (!$expectedCheckIn) {
            return;
        }

        $this->is_late = $this->check_in->gt($expectedCheckIn);
        $this->save();
    }

    public function checkEarlyDepartureStatus(): void
    {
        if (!$this->check_out || !$this->employee?->activeSchedule) {
            return;
        }

        $expectedCheckOut = $this->employee->getExpectedCheckOutTime($this->check_out);
        if (!$expectedCheckOut) {
            return;
        }

        $this->is_early_departure = $this->check_out->lt($expectedCheckOut);
        $this->save();
    }

    protected static function booted()
    {
        static::creating(function ($attendance) {
            if ($attendance->check_in) {
                $attendance->attendance_date = $attendance->check_in->toDateString();
                $attendance->check_in_time = $attendance->check_in->toTimeString();
            }
        });

        static::updating(function ($attendance) {
            if ($attendance->isDirty('check_in')) {
                $attendance->attendance_date = $attendance->check_in->toDateString();
                $attendance->check_in_time = $attendance->check_in->toTimeString();
            }
            if ($attendance->isDirty('check_out')) {
                $attendance->check_out_time = $attendance->check_out->toTimeString();
                $attendance->calculateTotalHours();
            }
        });

        static::saved(function ($attendance) {
            if ($attendance->isDirty(['check_in', 'check_out'])) {
                $attendance->updateWorkingHours();
                $attendance->checkLateStatus();
                $attendance->checkEarlyDepartureStatus();
            }
        });
    }
}

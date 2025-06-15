<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'check_in_time',
        'check_out_time',
        'working_hours_per_day',
        'rest_days',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'rest_days' => 'array',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isRestDay(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, $this->rest_days);
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return !$this->isRestDay($date);
    }

    public function isEffective(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_from && $date->startOfDay()->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->startOfDay()->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    public function getRestDaysTextAttribute(): string
    {
        $days = collect($this->rest_days)->map(function ($day) {
            return Carbon::create()->dayOfWeek($day)->format('l');
        })->sort()->values();

        return $days->join(', ');
    }

    public function getWorkingHoursAttribute(): string
    {
        return Carbon::parse($this->check_in_time)->format('H:i') . ' - ' . 
               Carbon::parse($this->check_out_time)->format('H:i');
    }
} 
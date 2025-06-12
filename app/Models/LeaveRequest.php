<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRequest extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'status',
        'reason',
        'notes',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('start_date', Carbon::now()->month)
                    ->whereYear('start_date', Carbon::now()->year);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    public function getTotalDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getIsOverlappingAttribute()
    {
        return static::where('employee_id', $this->employee_id)
            ->where('id', '!=', $this->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date]);
            })->exists();
    }

    // Helper Methods
    public function approve($approverId)
    {
        $this->status = 'approved';
        $this->approved_by = $approverId;
        $this->approved_at = now();
        $this->save();
    }

    public function reject($reason = null)
    {
        $this->status = 'rejected';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    public function cancel()
    {
        if ($this->status === 'pending') {
            $this->status = 'cancelled';
            $this->save();
        }
    }

    protected static function booted()
    {
        static::creating(function ($leaveRequest) {
            if (!$leaveRequest->status) {
                $leaveRequest->status = 'pending';
            }
        });
    }
} 
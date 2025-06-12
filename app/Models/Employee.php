<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'department_id',
        'position',
        'status',
        'join_date',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getEmploymentDurationAttribute()
    {
        return Carbon::parse($this->join_date)->diffForHumans(null, true);
    }

    public function getIsNewEmployeeAttribute()
    {
        return Carbon::parse($this->join_date)->diffInMonths() < 3;
    }

    // Helper Methods
    public function markAsInactive()
    {
        $this->status = 'inactive';
        $this->is_active = false;
        $this->save();
    }

    public function markAsActive()
    {
        $this->status = 'active';
        $this->is_active = true;
        $this->save();
    }

    public function transferToDepartment($departmentId)
    {
        $this->department_id = $departmentId;
        $this->save();
    }
}
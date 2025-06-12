<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    // Relationships
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereHas('employees', function ($query) {
            $query->where('status', 'active');
        });
    }

    // Accessors
    public function getEmployeeCountAttribute()
    {
        return $this->employees()->count();
    }

    public function getActiveEmployeeCountAttribute()
    {
        return $this->employees()->where('status', 'active')->count();
    }

    // Helper Methods
    public function getAttendanceStats($date = null)
    {
        $date = $date ?: now();
        $employeeIds = $this->employees()->pluck('id');

        return [
            'total' => $employeeIds->count(),
            'present' => Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('attendance_date', $date)
                ->where('status', 'present')
                ->count(),
            'absent' => Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('attendance_date', $date)
                ->where('status', 'absent')
                ->count(),
            'late' => Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('attendance_date', $date)
                ->whereTime('check_in_time', '>', '09:00:00')
                ->count()
        ];
    }

    public function getLeaveStats($startDate = null, $endDate = null)
    {
        $query = LeaveRequest::whereIn('employee_id', $this->employees()->pluck('id'));

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'approved' => $query->where('status', 'approved')->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'rejected' => $query->where('status', 'rejected')->count()
        ];
    }
} 
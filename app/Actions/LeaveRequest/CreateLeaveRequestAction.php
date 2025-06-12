<?php

namespace App\Actions\LeaveRequest;

use Lorisleiva\Actions\Concerns\AsAction;
use Carbon\Carbon;

class CreateLeaveRequestAction
{
    use AsAction;

    public function handle(array $data): array
    {
        // Calculate total days
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $totalDays = $endDate->diffInDays($startDate) + 1;

        return [
            'employee_id' => $data['employee_id'],
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ];
    }
} 
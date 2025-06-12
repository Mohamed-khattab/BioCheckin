<?php

namespace App\Actions\LeaveRequest;

use App\Models\LeaveRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Filament\Notifications\Notification;

class ApproveLeaveRequestAction
{
    use AsAction;

    public function handle(LeaveRequest $leaveRequest, string $approvedBy): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);

        // Send notification
        Notification::make()
            ->title('Leave request approved')
            ->success()
            ->send();

        return $leaveRequest;
    }
} 
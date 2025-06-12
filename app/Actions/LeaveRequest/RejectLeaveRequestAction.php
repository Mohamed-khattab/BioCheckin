<?php

namespace App\Actions\LeaveRequest;

use App\Models\LeaveRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Filament\Notifications\Notification;

class RejectLeaveRequestAction
{
    use AsAction;

    public function handle(LeaveRequest $leaveRequest, string $rejectionReason): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);

        // Send notification
        Notification::make()
            ->title('Leave request rejected')
            ->danger()
            ->send();

        return $leaveRequest;
    }
} 
<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Jmrashed\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Concerns\InteractsWithIO;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\FingerprintDevice;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsCommand;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class FetchAttendanceAction
{
    use AsAction;
    use InteractsWithIO;
    use AsCommand;

    public string $commandSignature = 'attendance:fetch {device?}';
    public string $commandDescription = 'Fetch attendance from ZKTeco device and store it in the database';

    protected $device;
    protected $startTime;
    protected $processedCount = 0;
    protected $errorCount = 0;

    public function handle(FingerprintDevice $fingerprintDevice)
    {
        $this->startTime = now();
        $this->device = $fingerprintDevice;
        
        try {
            Log::info("Starting attendance fetch for device: {$fingerprintDevice->name}");
            
            // Initialize ZKTeco with retry mechanism
            $zk = $this->initializeZKTeco($fingerprintDevice);
            if (!$zk) {
                $this->notifyError("Failed to connect to device {$fingerprintDevice->name}");
                return false;
            }

            // Fetch attendance data
            $attendanceLog = $zk->getAttendance();
            if (empty($attendanceLog)) {
                $this->notifyInfo("No new attendance records found for {$fingerprintDevice->name}");
                return true;
            }

            Log::info("Retrieved " . count($attendanceLog) . " records from device {$fingerprintDevice->name}");

            // Process records in batches
            $this->processAttendanceRecords($attendanceLog);

            // Update device sync time
            $fingerprintDevice->update(['updated_at' => now()]);

            // Disconnect device
            $zk->disconnect();

            // Send success notification
            $this->notifySuccess();

            return true;
        } catch (\Exception $e) {
            Log::error('FetchAttendanceAction error', [
                'device' => $fingerprintDevice->name,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->notifyError("Error fetching attendance: {$e->getMessage()}");
            return false;
        }
    }

    protected function initializeZKTeco(FingerprintDevice $device): ?ZKTeco
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $zk = new ZKTeco($device->ip, $device->port);
                if ($zk->connect()) {
                    if ($device->password) {
                        $zk->setPassword($device->password);
                    }
                    return $zk;
                }
            } catch (\Exception $e) {
                Log::warning("Connection attempt {$attempt} failed: {$e->getMessage()}");
                if ($attempt < 3) sleep(2);
            }
        }
        return null;
    }

    protected function processAttendanceRecords(array $attendanceLog): void
    {
        $batchSize = 100;
        
        foreach (array_chunk($attendanceLog, $batchSize) as $batch) {
            DB::beginTransaction();
            try {
                foreach ($batch as $log) {
                    // Ensure employee exists
                    $employee = Employee::firstOrCreate(
                        ['employee_id' => $log['id']],
                        ['name' => "Employee {$log['id']}"] // Default name
                    );

                    // Create or update attendance record
                    $attendance = Attendance::updateOrCreate(
                        [
                            'employee_id' => $log['id'],
                            'timestamp' => Carbon::parse($log['timestamp']),
                        ],
                        [
                            'state' => $log['state'],
                            'type' => $log['type'],
                        ]
                    );

                    $this->processedCount++;
                }
                
                DB::commit();
                Log::info("Processed batch of " . count($batch) . " records");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errorCount++;
                Log::error("Error processing batch", [
                    'error' => $e->getMessage(),
                    'batch_size' => count($batch)
                ]);
            }
        }
    }

    protected function notifySuccess(): void
    {
        $duration = now()->diffInSeconds($this->startTime);
        $message = "Successfully processed {$this->processedCount} records in {$duration} seconds";
        
        if ($this->errorCount > 0) {
            $message .= " ({$this->errorCount} errors encountered)";
        }

        Notification::make()
            ->title('Attendance Fetch Complete')
            ->body($message)
            ->success()
            ->send();
    }

    protected function notifyError(string $message): void
    {
        Notification::make()
            ->title('Attendance Fetch Failed')
            ->body($message)
            ->danger()
            ->send();
    }

    protected function notifyInfo(string $message): void
    {
        Notification::make()
            ->title('Attendance Fetch Info')
            ->body($message)
            ->info()
            ->send();
    }
}

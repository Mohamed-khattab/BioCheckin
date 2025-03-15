<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Jmrashed\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Concerns\InteractsWithIO;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsCommand;

class FetchAttendanceAction
{
    use AsAction;
    use InteractsWithIO;
    use AsCommand;
    public string $commandSignature = 'attendance:fetch';
    public string $commandDescription = 'Fetch attendance from ZKTeco device and store it in the database';

    public function handle()
    {
        echo "Fetching attendance.\n";
        try {
            $zk = new ZKTeco('10.0.0.202', 4370);

            if (!$zk->connect()) {
                Log::error('Failed to connect to ZKTeco device.');
                return;
            }

            Log::info('Connected to ZKTeco device, fetching attendance data.');
            $attendanceLog = $zk->getAttendance();
            Log::info('attendencce ', [$attendanceLog]);
            Log::info('Device disconnected.');

            if (!empty($attendanceLog)) {
                $batchData = [];
                $batchSize = 100;

                foreach (array_chunk($attendanceLog, $batchSize) as $batch) {
                    $batchData = array_map(function ($log) {
                        return [
                            'employee_id' => $log['id'],
                            'timestamp' => $log['timestamp'],
                            'state' => $log['state'],
                            'type' => $log['type'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }, $batch);

                    Attendance::upsert($batchData, ['employee_id', 'timestamp'], ['state', 'type', 'updated_at']);
                    Log::info("Inserted batch of " . count($batchData) . " attendance records.");
                }
            }
        } catch (\Exception $e) {
            Log::error('FetchAttendanceAction error', ['message' => $e->getMessage()]);
        }
    }
}

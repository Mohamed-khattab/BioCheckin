<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchAttendanceCommand extends Command
{
    protected $signature = 'attendance:fetch {device?} {--days=1}';
    protected $description = 'Fetch attendance records from biometric devices';

    public function handle()
    {
        $deviceId = $this->argument('device');
        $days = $this->option('days');

        $query = Device::query();
        if ($deviceId) {
            $query->where('id', $deviceId);
        }

        $devices = $query->get();

        foreach ($devices as $device) {
            try {
                $this->info("Processing device: {$device->name}");
                
                // Connect to device
                $zk = $this->connectToDevice($device);
                if (!$zk) {
                    $this->error("Could not connect to device {$device->name}");
                    continue;
                }

                // Get attendance logs
                $attendanceLogs = $zk->getAttendance();
                if (empty($attendanceLogs)) {
                    $this->info("No new attendance records found for device {$device->name}");
                    continue;
                }

                // Filter logs by date if days parameter is provided
                $startDate = Carbon::now()->subDays($days)->startOfDay();
                
                // Process logs in transaction batches
                $processedCount = 0;
                $errorCount = 0;
                
                collect($attendanceLogs)
                    ->filter(function ($log) use ($startDate) {
                        return Carbon::parse($log['timestamp'])->greaterThanOrEqualTo($startDate);
                    })
                    ->chunk(100)
                    ->each(function ($chunk) use ($device, &$processedCount, &$errorCount) {
                        DB::beginTransaction();
                        try {
                            foreach ($chunk as $log) {
                                // Find or create employee if needed
                                $employee = Employee::firstOrCreate(
                                    ['employee_id' => $log['id']],
                                    ['name' => "Employee {$log['id']}"] // Default name
                                );

                                $timestamp = Carbon::parse($log['timestamp']);
                                
                                // For check-out records, find the corresponding check-in
                                if ($log['state'] === 0) { // Check-out
                                    $attendance = Attendance::where('employee_id', $employee->id)
                                        ->whereDate('check_in', $timestamp->toDateString())
                                        ->whereNull('check_out')
                                        ->latest('check_in')
                                        ->first();
                                        
                                    if ($attendance) {
                                        $attendance->update([
                                            'check_out' => $timestamp,
                                            'device_id' => $device->id
                                        ]);
                                    } else {
                                        // If no check-in found, create a new record with both check-in and check-out
                                        Attendance::create([
                                            'employee_id' => $employee->id,
                                            'device_id' => $device->id,
                                            'check_in' => $timestamp,
                                            'check_out' => $timestamp,
                                            'status' => 'present'
                                        ]);
                                    }
                                } else { // Check-in
                                    Attendance::create([
                                        'employee_id' => $employee->id,
                                        'device_id' => $device->id,
                                        'check_in' => $timestamp,
                                        'status' => 'present'
                                    ]);
                                }

                                $processedCount++;
                            }
                            DB::commit();
                            
                            // Update device metrics
                            $device->updateRecordCounts();
                            
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $errorCount++;
                            Log::error("Error processing attendance batch: " . $e->getMessage());
                            $this->error("Error processing batch: " . $e->getMessage());
                        }
                    });

                $this->info("Processed {$processedCount} records with {$errorCount} errors for device {$device->name}");
                
                // Clear the device's attendance logs if successful
                if ($errorCount === 0) {
                    $zk->clearAttendance();
                }
                
                // Update device status
                $device->markAsOnline();
                
            } catch (\Exception $e) {
                Log::error("Device {$device->name} error: " . $e->getMessage());
                $this->error("Error processing device {$device->name}: " . $e->getMessage());
                $device->recordError($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    protected function connectToDevice($device)
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $zk = new \ZKLib($device->ip_address, $device->port ?? 4370);
                if ($zk->connect()) {
                    return $zk;
                }
            } catch (\Exception $e) {
                Log::warning("Connection attempt {$attempt} failed: {$e->getMessage()}");
                if ($attempt < 3) sleep(2);
            }
        }
        return null;
    }
} 
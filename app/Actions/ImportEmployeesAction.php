<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Illuminate\Console\Command;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportEmployeesAction
{
    use AsAction;
    use AsCommand;

    public string $commandSignature = 'employees:import {file}';
    public string $commandDescription = 'Import employees from an Excel file';

    public function handle(string $file)
    {
        $filePath = base_path($file);
        Log::info("Starting employee import from: " . $filePath);

        if (!file_exists($filePath)) {
            Log::error("File not found: $filePath");
            return "File not found: $filePath";
        }

        try {
            // Use PhpSpreadsheet directly instead of the Excel facade
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                Log::error("Invalid or empty Excel file.");
                return "Invalid or empty Excel file.";
            }

            $importCount = 0;
            
            foreach ($rows as $row) {
                if (!isset($row[0], $row[1]) || empty($row[0])) continue; 

                Employee::updateOrCreate(
                    ['employee_id' => $row[0]], 
                    ['name' => $row[1]]          
                );
                
                $importCount++;
            }

            $message = "✅ $importCount employees imported successfully.";
            Log::info($message);
            return $message;
        } catch (\Exception $e) {
            $errorMessage = "❌ Error importing employees: " . $e->getMessage();
            Log::error($errorMessage);
            return $errorMessage;
        }
    }

    public function asCommand(Command $command)
    {
        $file = $command->argument('file');
        $result = $this->handle($file);
        $command->info($result);
    }
}
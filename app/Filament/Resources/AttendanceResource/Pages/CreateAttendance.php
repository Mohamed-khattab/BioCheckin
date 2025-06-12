<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Employee;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the appropriate check_in or check_out based on the type
        if ($data['type'] === 'check_in') {
            $data['check_in'] = $data['datetime'];
            $data['check_out'] = null;
        } else {
            $data['check_out'] = $data['datetime'];
            // Find the latest check_in for this employee if it exists
            $latestAttendance = \App\Models\Attendance::where('employee_id', $data['employee_id'])
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->latest('check_in')
                ->first();
            if ($latestAttendance) {
                $data['check_in'] = $latestAttendance->check_in;
            } else {
                // If no check-in found, use the same time as check-out
                $data['check_in'] = $data['datetime'];
            }
        }

        // Remove temporary fields
        unset($data['datetime'], $data['type']);
        
        // Set default status
        $data['status'] = 'present';

        return $data;
    }

    protected function getFormSchema(): array 
    {
        return [
            Section::make('Quick Attendance Entry')
                ->description('Record manual attendance quickly')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('employee_id')
                                ->label('Employee')
                                ->options(Employee::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->preload()
                                ->columnSpan(2),
                                
                            DateTimePicker::make('datetime')
                                ->label('Date & Time')
                                ->seconds(false)
                                ->default(now())
                                ->required(),
                                
                            Select::make('type')
                                ->label('Record Type')
                                ->options([
                                    'check_in' => 'Check In',
                                    'check_out' => 'Check Out',
                                ])
                                ->required()
                                ->default('check_in'),
                        ]),
                ]),
        ];
    }
}

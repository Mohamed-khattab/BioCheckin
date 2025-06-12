<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class LatestAttendanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Latest Attendance Records';
    protected static ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->with('employee')
                    ->latest('check_in')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match($state) {
                        'check_in' => 'success',
                        'check_out' => 'danger',
                        default => 'warning'
                    }),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            1 => 'Fingerprint',
                            2 => 'Password',
                            3 => 'Card',
                            default => 'Other'
                        };
                    })
                    ->color('info'),
            ])
            ->defaultSort('check_in', 'desc');
    }
} 
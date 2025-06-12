<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Recent Activity';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->with('employee')
                    ->latest('check_in')
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'check_in' => 'Check In',
                            'check_out' => 'Check Out',
                            default => 'Unknown'
                        };
                    }),
                TextColumn::make('type')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            1 => 'Fingerprint',
                            2 => 'Password',
                            3 => 'Card',
                            default => 'Other'
                        };
                    }),
            ])
            ->defaultSort('check_in', 'desc');
    }
} 
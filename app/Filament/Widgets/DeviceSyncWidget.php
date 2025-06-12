<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class DeviceSyncWidget extends BaseWidget
{
    protected static ?string $heading = 'Device Sync Status';
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = '1';

    public function table(Table $table): Table
    {
        return $table
            ->query(Device::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('ip')
                    ->label('IP Address')
                    ->searchable(),
                    
                TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                    
                IconColumn::make('is_online')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('last_sync')
                    ->label('Last Sync')
                    ->dateTime()
                    ->sortable()
                    ->description(fn ($record) => 
                        $record->last_sync 
                            ? Carbon::parse($record->last_sync)->diffForHumans()
                            : 'Never synced'
                    ),
                    
                TextColumn::make('records_count')
                    ->label('Records Today')
                    ->counts('attendanceRecords', function ($query) {
                        return $query->whereDate('check_in', Carbon::today());
                    })
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated(false);
    }
} 
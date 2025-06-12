<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Carbon\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    
    protected static ?string $navigationGroup = 'Attendance Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                DateTimePicker::make('check_in')
                    ->required(),
                    
                DateTimePicker::make('check_out'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Check In')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('check_out')
                    ->label('Check Out')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Hours')
                    ->getStateUsing(function ($record) {
                        if (!$record->check_out) return '-';
                        $checkIn = Carbon::parse($record->check_in);
                        $checkOut = Carbon::parse($record->check_out);
                        return number_format($checkOut->diffInHours($checkIn, true), 1);
                    })
                    ->alignCenter(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(function ($record) {
                        if (!$record->check_out) return 'Present';
                        return 'Completed';
                    })
                    ->colors([
                        'success' => 'Completed',
                        'warning' => 'Present',
                    ]),
            ])
            ->defaultSort('check_in', 'desc')
            ->filters([
                SelectFilter::make('employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                    
                Filter::make('date')
                    ->form([
                        DateTimePicker::make('from'),
                        DateTimePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('check_in', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('check_in', '<=', $date),
                            );
                    }),
                    
                TernaryFilter::make('status')
                    ->placeholder('All Records')
                    ->trueLabel('Present')
                    ->falseLabel('Completed')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('check_out'),
                        false: fn (Builder $query) => $query->whereNotNull('check_out'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('60s')
            ->striped()
            ->paginated(25)
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}

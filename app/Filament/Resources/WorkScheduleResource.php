<?php

namespace App\Filament\Resources;

use App\Models\WorkSchedule;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\WorkScheduleResource\Pages;
use App\Filament\Resources\WorkScheduleResource\Widgets;
use Filament\Forms\Components\Actions\Action;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Employee Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Employee Information')
                    ->schema([
                        Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Schedule Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('check_in_time')
                                    ->required()
                                    ->seconds(false)
                                    ->helperText('Expected daily check-in time'),

                                TimePicker::make('check_out_time')
                                    ->required()
                                    ->seconds(false)
                                    ->helperText('Expected daily check-out time')
                                    ->afterOrEqual('check_in_time'),
                            ]),

                        Forms\Components\TextInput::make('working_hours_per_day')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(24)
                            ->step(0.5)
                            ->suffix('hours')
                            ->helperText('Standard working hours per day'),

                        Select::make('rest_days')
                            ->multiple()
                            ->options([
                                0 => 'Sunday',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                            ])
                            ->required()
                            ->minItems(1)
                            ->maxItems(2)
                            ->helperText('Select 1 or 2 rest days')
                            ->preload()
                            ->searchable()
                            ->suffixAction(
                                Action::make('standardWeekend')
                                    ->icon('heroicon-m-calendar')
                                    ->tooltip('Set to standard weekend (Sat-Sun)')
                                    ->action(function ($set) {
                                        $set('rest_days', [0, 6]); // Sunday and Saturday
                                    })
                            ),
                    ]),

                Section::make('Schedule Validity')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->helperText('Is this schedule currently active?'),

                                DatePicker::make('effective_from')
                                    ->nullable()
                                    ->helperText('When does this schedule start?')
                                    ->default(now()),

                                DatePicker::make('effective_until')
                                    ->nullable()
                                    ->helperText('When does this schedule end?')
                                    ->after('effective_from'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('working_hours')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rest_days_text')
                    ->label('Rest Days'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('effective_from')
                    ->date()
                    ->sortable(),

                TextColumn::make('effective_until')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'name'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('effective_date')
                    ->form([
                        DatePicker::make('effective_date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['effective_date'],
                                fn ($query, $date) => $query
                                    ->where('effective_from', '<=', $date)
                                    ->where(function ($query) use ($date) {
                                        $query->whereNull('effective_until')
                                            ->orWhere('effective_until', '>=', $date);
                                    })
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ScheduleStatsWidget::class,
            Widgets\ScheduleDistributionWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkSchedules::route('/'),
            'create' => Pages\CreateWorkSchedule::route('/create'),
            'edit' => Pages\EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}
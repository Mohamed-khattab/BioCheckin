<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Forms\Components\DateRangePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Actions\LeaveRequest\ApproveLeaveRequestAction;
use App\Actions\LeaveRequest\RejectLeaveRequestAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Carbon\Carbon;
use Filament\Support\Colors\Color;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationGroup = 'Employee Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Card::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Leave Request Details')
                                    ->description('Enter the leave request information')
                                    ->schema([
                                        Select::make('employee_id')
                                            ->relationship('employee', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(2),
                                            
                                        Select::make('leave_type_id')
                                            ->relationship('leaveType', 'name')
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(2),

                                        Hidden::make('start_date')
                                            ->required(),
                                            
                                        Hidden::make('end_date')
                                            ->required(),

                                        DateRangePicker::make('date_range')
                                            ->label('Leave Period')
                                            ->required()
                                            ->live()
                                            ->columnSpan(2)
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                if (is_array($state) && isset($state['start']) && isset($state['end'])) {
                                                    $set('start_date', $state['start']);
                                                    $set('end_date', $state['end']);
                                                }
                                            }),

                                        Textarea::make('reason')
                                            ->label('Reason for Leave')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpan(2),
                                    ]),
                            ]),
                            
                        Card::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Leave Summary')
                                    ->description('Overview of your leave request')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Placeholder::make('total_days')
                                                    ->label('Total Days')
                                                    ->content(function ($get) {
                                                        if (!$get('start_date') || !$get('end_date')) {
                                                            return view('filament.components.summary-stat', [
                                                                'icon' => 'heroicon-o-calendar',
                                                                'label' => 'Days',
                                                                'value' => '0',
                                                                'color' => 'gray',
                                                            ]);
                                                        }

                                                        $start = Carbon::parse($get('start_date'));
                                                        $end = Carbon::parse($get('end_date'));
                                                        
                                                        // Include both start and end dates in the count
                                                        $days = $start->copy()->startOfDay()
                                                            ->diffInDays($end->copy()->endOfDay()) + 1;

                                                        return view('filament.components.summary-stat', [
                                                            'icon' => 'heroicon-o-calendar',
                                                            'label' => 'Days',
                                                            'value' => $days,
                                                            'color' => 'primary',
                                                        ]);
                                                    })
                                                    ->columnSpan(2),

                                                Placeholder::make('status')
                                                    ->label('Request Status')
                                                    ->content(function ($get, $record) {
                                                        $status = $record?->status ?? 'pending';
                                                        $statusConfig = match(strtolower($status)) {
                                                            'approved' => [
                                                                'label' => 'Approved',
                                                                'color' => 'success',
                                                            ],
                                                            'rejected' => [
                                                                'label' => 'Rejected',
                                                                'color' => 'danger',
                                                            ],
                                                            default => [
                                                                'label' => 'Pending',
                                                                'color' => 'warning',
                                                            ],
                                                        };

                                                        return view('filament.components.status-badge', [
                                                            'status' => $statusConfig['label'],
                                                            'color' => $statusConfig['color'],
                                                        ]);
                                                    })
                                                    ->columnSpan(2),

                                                Placeholder::make('date_range_display')
                                                    ->label('Selected Period')
                                                    ->content(function ($get) {
                                                        if (!$get('start_date') || !$get('end_date')) {
                                                            return view('filament.components.empty-state', [
                                                                'message' => 'No dates selected',
                                                            ]);
                                                        }
                                                        
                                                        $start = Carbon::parse($get('start_date'));
                                                        $end = Carbon::parse($get('end_date'));
                                                        
                                                        return view('filament.components.date-range-display', [
                                                            'startDate' => $start->format('M d, Y'),
                                                            'endDate' => $end->format('M d, Y'),
                                                            'icon' => 'heroicon-o-calendar-days',
                                                        ]);
                                                    })
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('leaveType.name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('total_days')
                    ->sortable(),
                    
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('approve')
                    ->action(function (LeaveRequest $record) {
                        ApproveLeaveRequestAction::run($record, auth()->user()->name);
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
                    
                Action::make('reject')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        RejectLeaveRequestAction::run($record, $data['rejection_reason']);
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
                    
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
} 
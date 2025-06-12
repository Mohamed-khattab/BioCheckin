<?php

namespace App\Filament\Resources;

use App\Actions\FetchAttendanceAction;
use App\Filament\Resources\FingerPrintDeviceResource\Pages;
use App\Filament\Resources\FingerPrintDeviceResource\RelationManagers;
use App\Models\FingerprintDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Filament\Support\Colors\Color;

class FingerPrintDeviceResource extends Resource
{
    protected static ?string $model = FingerprintDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'Fingerprint Devices';

    protected static ?string $modelLabel = 'Fingerprint Device';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Device Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Enter device name')
                                    ->columnSpan(1),
                                
                                TextInput::make('ip')
                                    ->required()
                                    ->placeholder('192.168.1.100')
                                    ->ipv4()
                                    ->columnSpan(1),

                                TextInput::make('port')
                                    ->required()
                                    ->placeholder('4370')
                                    ->numeric()
                                    ->default('4370')
                                    ->columnSpan(1),

                                TextInput::make('password')
                                    ->password()
                                    ->placeholder('Device password (if required)')
                                    ->columnSpan(1),

                                TextInput::make('user')
                                    ->numeric()
                                    ->placeholder('User ID (if required)')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip')
                    ->searchable()
                    ->label('IP Address'),
                TextColumn::make('port')
                    ->sortable(),
                TextColumn::make('user')
                    ->label('User ID')
                    ->formatStateUsing(fn ($state) => $state ?? 'Not Set'),
                TextColumn::make('updated_at')
                    ->label('Last Sync')
                    ->dateTime()
                    ->sortable()
                    ->description(fn ($record) => $record->updated_at ? 'Last successful sync' : 'Never synced'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('fetch_attendance')
                    ->label('Fetch Attendance')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color(Color::Emerald)
                    ->requiresConfirmation()
                    ->modalHeading('Fetch Attendance Records')
                    ->modalDescription('Are you sure you want to fetch attendance records from this device?')
                    ->modalSubmitActionLabel('Yes, fetch records')
                    ->action(function (FingerprintDevice $record) {
                        return app(FetchAttendanceAction::class)->handle($record);
                    })
                    ->successNotificationTitle('Attendance fetch initiated'),
                
                Action::make('test_connection')
                    ->label('Test Connection')
                    ->icon('heroicon-m-signal')
                    ->color(Color::Amber)
                    ->action(function (FingerprintDevice $record) {
                        try {
                            $zk = new \ZKTeco\ZKTeco($record->ip, $record->port);
                            if ($zk->connect()) {
                                if ($record->password) {
                                    $zk->setPassword($record->password);
                                }
                                $zk->disconnect();
                                return true;
                            }
                        } catch (\Exception $e) {
                            return false;
                        }
                        return false;
                    })
                    ->successNotificationTitle('Connection successful')
                    ->failureNotificationTitle('Connection failed'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('fetch_all_attendance')
                        ->label('Fetch Attendance')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->requiresConfirmation()
                        ->modalHeading('Fetch Attendance Records')
                        ->modalDescription('Are you sure you want to fetch attendance records from all selected devices?')
                        ->modalSubmitActionLabel('Yes, fetch records')
                        ->action(function (array $records) {
                            $action = app(FetchAttendanceAction::class);
                            $successCount = 0;
                            $failCount = 0;

                            foreach ($records as $record) {
                                $result = $action->handle(FingerprintDevice::find($record['id']));
                                if ($result) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                }
                            }

                            if ($failCount > 0) {
                                return "Completed with {$successCount} successes and {$failCount} failures";
                            }
                            return "Successfully processed {$successCount} devices";
                        })
                        ->successNotificationTitle('Bulk attendance fetch completed'),
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
            'index' => Pages\ListFingerPrintDevices::route('/'),
            'create' => Pages\CreateFingerPrintDevice::route('/create'),
            'edit' => Pages\EditFingerPrintDevice::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

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

class FingerPrintDeviceResource extends Resource
{
    protected static ?string $model = FingerprintDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->unique(),
                TextInput::make('ip')->required(),
                TextInput::make('port')->nullable(),
                TextInput::make('password')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
                TextColumn::make('ip')->searchable(),
                TextColumn::make('port')->sortable(),
                // TextColumn::

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFingerPrintDevices::route('/'),
            'create' => Pages\CreateFingerPrintDevice::route('/create'),
            'edit' => Pages\EditFingerPrintDevice::route('/{record}/edit'),
        ];
    }
}

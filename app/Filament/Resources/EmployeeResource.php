<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Widgets\Widgets;
use App\Filament\Widgets\EmployeeStatsWidget;
use App\Filament\Widgets\LatestEmployeesWidget;
use App\Filament\Widgets\DepartmentDistributionWidget;
use App\Filament\Widgets\EmployeeAttendanceWidget;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('employee_id')->required()->unique(),
                TextInput::make('name')->required(),
                TextInput::make('department')->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return  $table->columns([
            TextColumn::make('employee_id')->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('department')->sortable(),
        ])->filters([
            Filter::make('department'),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeStatsWidget::class,
            DepartmentDistributionWidget::class,
            EmployeeAttendanceWidget::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}

<?php

namespace App\Filament\Resources\WorkScheduleResource\Pages;

use App\Filament\Resources\WorkScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkSchedule extends EditRecord
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 
<?php

namespace App\Filament\Resources\FingerPrintDeviceResource\Pages;

use App\Filament\Resources\FingerPrintDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFingerPrintDevices extends ListRecords
{
    protected static string $resource = FingerPrintDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

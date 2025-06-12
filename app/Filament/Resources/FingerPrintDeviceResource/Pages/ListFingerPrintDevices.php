<?php

namespace App\Filament\Resources\FingerPrintDeviceResource\Pages;

use App\Filament\Resources\FingerPrintDeviceResource;
use App\Filament\Widgets\FingerprintDeviceStatsWidget;
use App\Filament\Widgets\FingerprintDeviceActivityWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFingerPrintDevices extends ListRecords
{
    protected static string $resource = FingerPrintDeviceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FingerprintDeviceStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            FingerprintDeviceActivityWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

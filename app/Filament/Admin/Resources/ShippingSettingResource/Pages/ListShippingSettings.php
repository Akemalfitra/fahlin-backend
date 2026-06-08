<?php

namespace App\Filament\Admin\Resources\ShippingSettingResource\Pages;

use App\Filament\Admin\Resources\ShippingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShippingSettings extends ListRecords
{
    protected static string $resource = ShippingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

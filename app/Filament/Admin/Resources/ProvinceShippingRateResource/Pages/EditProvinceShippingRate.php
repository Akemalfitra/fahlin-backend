<?php

namespace App\Filament\Admin\Resources\ProvinceShippingRateResource\Pages;

use App\Filament\Admin\Resources\ProvinceShippingRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProvinceShippingRate extends EditRecord
{
    protected static string $resource = ProvinceShippingRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

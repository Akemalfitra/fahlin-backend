<?php

namespace App\Filament\Admin\Resources\VoucherAdResource\Pages;

use App\Filament\Admin\Resources\VoucherAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVoucherAds extends ListRecords
{
    protected static string $resource = VoucherAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Admin\Resources\VoucherAdResource\Pages;

use App\Filament\Admin\Resources\VoucherAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVoucherAd extends EditRecord
{
    protected static string $resource = VoucherAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

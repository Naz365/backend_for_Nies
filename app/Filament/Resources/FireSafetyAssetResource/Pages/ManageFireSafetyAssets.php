<?php

namespace App\Filament\Resources\FireSafetyAssetResource\Pages;

use App\Filament\Resources\FireSafetyAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFireSafetyAssets extends ManageRecords
{
    protected static string $resource = FireSafetyAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

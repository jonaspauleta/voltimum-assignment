<?php

declare(strict_types=1);

namespace App\Filament\Resources\Manufacturers\Pages;

use App\Filament\Resources\Manufacturers\ManufacturerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturer extends EditRecord
{
    protected static string $resource = ManufacturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

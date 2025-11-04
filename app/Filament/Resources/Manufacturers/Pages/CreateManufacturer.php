<?php

declare(strict_types=1);

namespace App\Filament\Resources\Manufacturers\Pages;

use App\Filament\Resources\Manufacturers\ManufacturerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateManufacturer extends CreateRecord
{
    protected static string $resource = ManufacturerResource::class;
}

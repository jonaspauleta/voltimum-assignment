<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors\Pages;

use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDistributor extends CreateRecord
{
    protected static string $resource = DistributorResource::class;
}

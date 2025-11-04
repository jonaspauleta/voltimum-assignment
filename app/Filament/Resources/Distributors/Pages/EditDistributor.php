<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors\Pages;

use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditDistributor extends EditRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

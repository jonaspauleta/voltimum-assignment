<?php

declare(strict_types=1);

namespace App\Filament\Resources\Distributors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class DistributorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}

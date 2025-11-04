<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('manufacturer_id')
                    ->relationship('manufacturer', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('ean')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}

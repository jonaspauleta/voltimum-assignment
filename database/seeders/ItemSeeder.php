<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

final class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::factory()->count(10)->create();
    }
}

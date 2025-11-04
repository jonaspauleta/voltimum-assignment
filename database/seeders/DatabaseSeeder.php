<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\Item;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Test User',
                'email' => 'test@voltimum.test',
                'is_admin' => true,
            ]);

        Manufacturer::factory()->count(4)->create()->each(function ($m): void {
            Product::factory()->count(5)->create([
                'manufacturer_id' => $m->id,
            ])->each(function ($p): void {
                $distributors = Distributor::factory()->count(2)->create();
                foreach ($distributors as $d) {
                    Item::factory()->create([
                        'product_id' => $p->id,
                        'distributor_id' => $d->id,
                    ]);
                }
            });
        });
    }
}

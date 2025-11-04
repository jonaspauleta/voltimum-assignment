<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Seeder;

final class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Distributor::factory()->count(10)->create();
    }
}

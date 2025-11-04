<?php

declare(strict_types=1);

use App\Livewire\Products\Index;
use App\Models\Distributor;
use App\Models\Item;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Create authenticated user
    $this->user = User::factory()->create();

    // Flush Scout index before each test
    Product::query()->searchable();

    // Create test data
    $this->manufacturers = Manufacturer::factory()->count(3)->create();
    $this->distributors = Distributor::factory()->count(3)->create();

    $this->products = collect();

    // Create products for first manufacturer
    $this->products = $this->products->merge(
        Product::factory()
            ->count(5)
            ->for($this->manufacturers[0])
            ->create()
    );

    // Create products for second manufacturer
    $this->products = $this->products->merge(
        Product::factory()
            ->count(3)
            ->for($this->manufacturers[1])
            ->create()
    );

    // Create products for third manufacturer
    $this->products = $this->products->merge(
        Product::factory()
            ->count(2)
            ->for($this->manufacturers[2])
            ->create()
    );

    // Create items (distributor relationships) for each product
    $this->products->each(function (Product $product, int $index): void {
        // Assign different distributors to products
        $distributor = $this->distributors[$index % 3];

        Item::factory()
            ->for($product)
            ->for($distributor)
            ->create();
    });

    // Make products searchable
    $this->products->searchable();

    // Process queue to ensure indexing is complete
    Artisan::call('queue:work', ['--stop-when-empty' => true]);
});

afterEach(function (): void {
    // Clean up Scout index
    Product::query()->unsearchable();
});

test('products index page renders', function (): void {
    actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('products are displayed on the page', function (): void {
    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->assertSee($this->products->first()->name)
        ->assertSee($this->products->last()->name);
});

test('search filters products by name', function (): void {
    $searchProduct = $this->products->first();

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->set('search', $searchProduct->name)
        ->assertSee($searchProduct->name)
        ->assertSet('search', $searchProduct->name);
});

test('search filters products by ean', function (): void {
    $product = $this->products->first();

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->set('search', $product->ean)
        ->assertSee($product->name);
});

test('manufacturer filter works', function (): void {
    $manufacturer = $this->manufacturers[0];
    $productsWithManufacturer = $this->products->where('manufacturer_id', $manufacturer->id);
    $productsWithoutManufacturer = $this->products->where('manufacturer_id', '!=', $manufacturer->id);

    Livewire::actingAs($this->user)->test(Index::class)
        ->set('selectedManufacturers', [$manufacturer->name])
        ->assertSee($productsWithManufacturer->first()->name)
        ->call('$refresh');

    // Verify filtered count is correct
    $test = Livewire::test(Index::class)
        ->set('selectedManufacturers', [$manufacturer->name]);

    $products = $test->get('products');

    expect($products->total())->toBe($productsWithManufacturer->count());
});

test('distributor filter works', function (): void {
    $distributor = $this->distributors[0];
    $productsWithDistributor = $this->products->filter(fn ($product) => $product->items->contains('distributor_id', $distributor->id));

    Livewire::actingAs($this->user)->test(Index::class)
        ->set('selectedDistributors', [$distributor->name])
        ->assertSee($productsWithDistributor->first()->name);
});

test('multiple manufacturer filters work together', function (): void {
    $manufacturer1 = $this->manufacturers[0];
    $manufacturer2 = $this->manufacturers[1];

    $test = Livewire::test(Index::class)
        ->set('selectedManufacturers', [$manufacturer1->name, $manufacturer2->name]);

    $products = $test->get('products');
    $expectedCount = $this->products->whereIn('manufacturer_id', [$manufacturer1->id, $manufacturer2->id])->count();

    expect($products->total())->toBe($expectedCount);
});

test('search and filters work together', function (): void {
    $manufacturer = $this->manufacturers[0];
    $product = $this->products->where('manufacturer_id', $manufacturer->id)->first();

    Livewire::actingAs($this->user)->test(Index::class)
        ->set('search', $product->name)
        ->set('selectedManufacturers', [$manufacturer->name])
        ->assertSee($product->name);
});

test('facets are always visible', function (): void {
    $test = Livewire::test(Index::class);

    $facets = $test->get('facets');

    expect($facets)->toBeArray()
        ->and($facets)->toHaveKey('manufacturer_name')
        ->and($facets)->toHaveKey('distributor_names')
        ->and($facets['manufacturer_name'])->not->toBeEmpty()
        ->and($facets['distributor_names'])->not->toBeEmpty();
});

test('facets remain stable when filters are selected', function (): void {
    $test = Livewire::test(Index::class);

    $initialFacets = $test->get('facets');
    $initialMfgCount = count($initialFacets['manufacturer_name']);

    $test->set('selectedManufacturers', [$this->manufacturers[0]->name]);

    $filteredFacets = $test->get('facets');
    $filteredMfgCount = count($filteredFacets['manufacturer_name']);

    expect($initialMfgCount)->toBe($filteredMfgCount);
});

test('clear all filters works', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->set('search', 'test search')
        ->set('selectedManufacturers', [$this->manufacturers[0]->name])
        ->set('selectedDistributors', [$this->distributors[0]->name])
        ->call('clearAll')
        ->assertSet('search', '')
        ->assertSet('selectedManufacturers', [])
        ->assertSet('selectedDistributors', []);
});

test('clear filters only clears manufacturer and distributor filters', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->set('search', 'test search')
        ->set('selectedManufacturers', [$this->manufacturers[0]->name])
        ->set('selectedDistributors', [$this->distributors[0]->name])
        ->call('clearFilters')
        ->assertSet('search', 'test search')
        ->assertSet('selectedManufacturers', [])
        ->assertSet('selectedDistributors', []);
});

test('has active filters returns true when search is set', function (): void {
    $test = Livewire::test(Index::class)
        ->set('search', 'test');

    expect($test->get('hasActiveFilters'))->toBeTrue();
});

test('has active filters returns true when manufacturer filter is set', function (): void {
    $test = Livewire::test(Index::class)
        ->set('selectedManufacturers', [$this->manufacturers[0]->name]);

    expect($test->get('hasActiveFilters'))->toBeTrue();
});

test('has active filters returns true when distributor filter is set', function (): void {
    $test = Livewire::test(Index::class)
        ->set('selectedDistributors', [$this->distributors[0]->name]);

    expect($test->get('hasActiveFilters'))->toBeTrue();
});

test('has active filters returns false when no filters are set', function (): void {
    $test = Livewire::test(Index::class);

    expect($test->get('hasActiveFilters'))->toBeFalse();
});

test('search resets pagination', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->call('gotoPage', 2)
        ->set('search', 'test')
        ->assertSet('paginators.page', 1);
});

test('manufacturer filter resets pagination', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->call('gotoPage', 2)
        ->set('selectedManufacturers', [$this->manufacturers[0]->name])
        ->assertSet('paginators.page', 1);
});

test('distributor filter resets pagination', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->call('gotoPage', 2)
        ->set('selectedDistributors', [$this->distributors[0]->name])
        ->assertSet('paginators.page', 1);
});

test('products show manufacturer information', function (): void {
    $product = $this->products->first();

    Livewire::actingAs($this->user)->test(Index::class)
        ->assertSee($product->manufacturer->name);
});

test('products show items count', function (): void {
    actingAs($this->user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('distributor');
});

test('empty search shows no results message', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->set('search', 'nonexistentproductname12345')
        ->assertSee('No products found');
});

test('filters sidebar is always visible', function (): void {
    actingAs($this->user)
        ->get(route('products.index'))
        ->assertSee('Filters')
        ->assertSee('Manufacturers')
        ->assertSee('Distributors');
});

test('facets show correct counts', function (): void {
    $test = Livewire::test(Index::class);
    $facets = $test->get('facets');

    $manufacturer = $this->manufacturers[0];
    $expectedCount = $this->products->where('manufacturer_id', $manufacturer->id)->count();

    expect($facets['manufacturer_name'][$manufacturer->name] ?? 0)->toBe($expectedCount);
});

test('url parameters are set correctly', function (): void {
    Livewire::actingAs($this->user)->test(Index::class)
        ->set('search', 'test')
        ->set('selectedManufacturers', ['Manufacturer 1'])
        ->assertSetStrict('search', 'test')
        ->assertSetStrict('selectedManufacturers', ['Manufacturer 1']);
});

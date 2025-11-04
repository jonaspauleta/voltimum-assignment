<?php

declare(strict_types=1);

use App\Models\Distributor;
use App\Models\Item;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Flush search index
    Product::removeAllFromSearch();

    // Create a seed product with all relationships to establish the full Typesense schema
    // This ensures nested fields like items.sku and items.distributor.name are in the schema
    $seed = Product::factory()
        ->for(Manufacturer::factory())
        ->has(Item::factory()->for(Distributor::factory()))
        ->create();

    $seed->loadMissing(['manufacturer', 'items.distributor']);
    $seed->searchable();

    // Remove it so tests start with clean slate
    $seed->unsearchable();
    $seed->delete();
});

afterEach(function (): void {
    Product::removeAllFromSearch();
});

test('products can be searched by name', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->has(Item::factory()->for(Distributor::factory()))
        ->create(['name' => 'Unique Product Name']);

    $product = $product->loadMissing(['manufacturer', 'items.distributor']);

    $results = Product::search('Unique')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($product->id)
        ->and($results->first()->name)->toBe('Unique Product Name');
});

test('products can be searched by slug', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->has(Item::factory()->for(Distributor::factory()))
        ->create(['slug' => 'special-unique-slug']);

    $results = Product::search('special-unique-slug')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('products can be searched by ean', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['ean' => '1234567890123']);

    $results = Product::search('1234567890123')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('products can be searched by description', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['description' => 'This product has a very distinctive description feature']);

    $results = Product::search('distinctive')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('products can be searched by manufacturer name', function (): void {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Acme Corporation']);
    $product = Product::factory()->for($manufacturer)->create();

    $results = Product::search('Acme')
        ->options(['query_by' => 'manufacturer.name'])
        ->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('products can be searched by manufacturer slug', function (): void {
    $manufacturer = Manufacturer::factory()->create(['slug' => 'xyz-manufacturing']);
    $product = Product::factory()->for($manufacturer)->create();

    $results = Product::search('xyz')
        ->options(['query_by' => 'manufacturer.slug'])
        ->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('product is automatically indexed when created via observer', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Auto Indexed Product']);

    $results = Product::search('Auto Indexed')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('search returns empty collection when no matches found', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Test Product']);

    $results = Product::search('NonExistentProductXYZ123')->get();

    expect($results)->toBeEmpty();
});

test('search with multiple words finds correct product', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Premium Wireless Headphones']);

    $results = Product::search('Wireless Headphones')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('search is case insensitive', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'CaseSensitiveProduct']);

    $lowerResults = Product::search('casesensitive')->get();
    $upperResults = Product::search('CASESENSITIVE')->get();

    expect($lowerResults->pluck('id'))->toContain($product->id)
        ->and($upperResults->pluck('id'))->toContain($product->id);
});

test('multiple products can be found with same manufacturer', function (): void {
    $manufacturer = Manufacturer::factory()->create(['name' => 'TechCorp']);

    $product1 = Product::factory()->for($manufacturer)->create(['name' => 'Product One']);
    $product2 = Product::factory()->for($manufacturer)->create(['name' => 'Product Two']);
    $product3 = Product::factory()->for($manufacturer)->create(['name' => 'Product Three']);

    $results = Product::search('TechCorp')
        ->options(['query_by' => 'manufacturer.name'])
        ->get();

    expect($results)->toHaveCount(3)
        ->and($results->pluck('id'))->toContain($product1->id, $product2->id, $product3->id);
});

test('search by partial word matches product', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Extraordinary Product']);

    $results = Product::search('Extra')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

test('search works with products that have no items', function (): void {
    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Product Without Items']);

    $results = Product::search('Without Items')->get();

    expect($results->pluck('id'))->toContain($product->id);
});

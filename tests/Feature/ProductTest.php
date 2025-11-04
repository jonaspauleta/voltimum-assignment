<?php

declare(strict_types=1);

use App\Livewire\Products\Index;
use App\Models\Distributor;
use App\Models\Item;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Initialize Typesense schema with a complete product (including items)
    Product::removeAllFromSearch();

    $schemaProduct = Product::factory()
        ->for(Manufacturer::factory())
        ->has(Item::factory()->for(Distributor::factory()))
        ->create();

    $schemaProduct->loadMissing(['manufacturer', 'items.distributor']);
    $schemaProduct->searchable();
    $schemaProduct->unsearchable();
    $schemaProduct->delete();
});

afterEach(function (): void {
    Product::removeAllFromSearch();
});

test('guests cannot access product index', function (): void {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view product index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('Products')
        ->assertSee('Search and browse our product catalog');
});

test('product index displays all products', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create();

    $product1 = Product::factory()->for($manufacturer)->create([
        'name' => 'First Product',
    ]);

    $product2 = Product::factory()->for($manufacturer)->create([
        'name' => 'Second Product',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('First Product')
        ->assertSee('Second Product');
});

test('product index search works in real-time', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create(['name' => 'Acme Corp']);

    $product1 = Product::factory()->for($manufacturer)->create([
        'name' => 'Widget Alpha',
    ]);

    $product2 = Product::factory()->for($manufacturer)->create([
        'name' => 'Gadget Beta',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Widget Alpha')
        ->assertSee('Gadget Beta')
        ->set('search', 'Widget')
        ->assertSee('Widget Alpha')
        ->assertDontSee('Gadget Beta')
        ->assertSee('result');
});

test('product index shows manufacturer name', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create(['name' => 'Super Manufacturer']);

    Product::factory()->for($manufacturer)->create([
        'name' => 'Test Product',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Test Product')
        ->assertSee('Super Manufacturer');
});

test('product index shows no products message when empty', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('No products found');
});

test('product index shows no results when search has no matches', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Test Product']);

    // Index the product
    $product->load(['manufacturer', 'items.distributor'])->searchable();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'NonExistentProductXYZ')
        ->assertSee('No products found')
        ->assertSee('Try adjusting your search terms');
});

test('product index can clear search', function (): void {
    $user = User::factory()->create();

    Product::factory()
        ->for(Manufacturer::factory())
        ->create(['name' => 'Test Product']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Test')
        ->assertSee('Clear search')
        ->call('clearSearch')
        ->assertDontSee('Clear search');
});

test('guests cannot view product details', function (): void {
    $product = Product::factory()->for(Manufacturer::factory())->create();

    $this->get(route('products.show', $product))
        ->assertRedirect(route('login'));
});

test('authenticated users can view product details', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);

    $product = Product::factory()->for($manufacturer)->create([
        'name' => 'Test Product',
        'description' => 'Product description',
        'ean' => '1234567890',
    ]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Test Product')
        ->assertSee('Product description')
        ->assertSee('1234567890')
        ->assertSee('Test Manufacturer');
});

test('product show page displays distributor information', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $distributor = Distributor::factory()->create(['name' => 'Global Distributor']);

    $product = Product::factory()->for($manufacturer)->create();

    Item::factory()->for($product)->for($distributor)->create([
        'sku' => 'SKU-123',
        'price' => 99.99,
    ]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Global Distributor')
        ->assertSee('SKU-123')
        ->assertSee('99.99');
});

test('product show page displays item price', function (): void {
    $user = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $distributor = Distributor::factory()->create();

    $product = Product::factory()->for($manufacturer)->create();

    Item::factory()->for($product)->for($distributor)->create([
        'price' => 123.45,
    ]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('123.45');
});

test('product show page shows message when no distributors', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()->for(Manufacturer::factory())->create();

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('not currently available from any distributors');
});

test('product show page has back to products link', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()->for(Manufacturer::factory())->create();

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Back to Products');
});

@php
$products = $this->products;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl" class="font-semibold text-neutral-900 dark:text-neutral-100">
            Products
        </flux:heading>
        <flux:text class="mt-1 text-neutral-600 dark:text-neutral-400">
            Search and browse our product catalog
        </flux:text>
    </div>

    {{-- Search Bar --}}
    <div class="relative w-full max-w-2xl">
        <flux:input wire:model.live.debounce.300ms="search" type="search" placeholder="Search products..."
            icon="magnifying-glass" class="w-full" />
        <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
            <flux:icon.arrow-path class="size-5 animate-spin text-neutral-400" />
        </div>
    </div>

    {{-- Active Filters Badge --}}
    @if($this->hasActiveFilters)
    <div class="flex items-center gap-2">
        <flux:badge variant="outline">
            {{ $products->total() }} results
        </flux:badge>
        <flux:button wire:click="clearAll" variant="ghost" size="sm" icon="x-mark">
            Clear all
        </flux:button>
    </div>
    @endif

    {{-- Main Layout: Sidebar + Content --}}
    <div class="flex gap-6">
        {{-- Filters Sidebar - ALWAYS SHOW --}}
        <aside class="w-64 shrink-0">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Filters</flux:heading>
                    @if(!empty($selectedManufacturers) || !empty($selectedDistributors))
                    <flux:button wire:click="clearFilters" variant="ghost" size="xs" icon="x-mark">
                        Clear
                    </flux:button>
                    @endif
                </div>

                {{-- Manufacturers --}}
                @if(!empty($this->facets['manufacturer_name']))
                <div class="mb-6">
                    <flux:text class="mb-3 font-semibold">Manufacturers</flux:text>
                    <div class="space-y-2">
                        @foreach($this->facets['manufacturer_name'] as $name => $count)
                        <label class="flex cursor-pointer items-center gap-2" wire:key="mfg-{{ Str::slug($name) }}">
                            <flux:checkbox wire:model.live="selectedManufacturers" value="{{ $name }}" />
                            <flux:text class="flex-1 text-sm">{{ $name }}</flux:text>
                            <flux:badge size="sm" variant="outline">{{ $count }}</flux:badge>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Distributors --}}
                @if(!empty($this->facets['distributor_names']))
                <div>
                    <flux:text class="mb-3 font-semibold">Distributors</flux:text>
                    <div class="max-h-96 space-y-2 overflow-y-auto">
                        @foreach($this->facets['distributor_names'] as $name => $count)
                        <label class="flex cursor-pointer items-center gap-2" wire:key="dist-{{ Str::slug($name) }}">
                            <flux:checkbox wire:model.live="selectedDistributors" value="{{ $name }}" />
                            <flux:text class="flex-1 text-sm">{{ $name }}</flux:text>
                            <flux:badge size="sm" variant="outline">{{ $count }}</flux:badge>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- No Facets Message --}}
                @if(empty($this->facets['manufacturer_name']) && empty($this->facets['distributor_names']))
                <flux:text class="text-sm text-neutral-500">
                    No filters available
                </flux:text>
                @endif
            </div>
        </aside>

        {{-- Products Grid --}}
        <div class="flex-1">
            <div wire:loading.remove wire:target="search,selectedManufacturers,selectedDistributors">
                @if($products->isEmpty())
                <div
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 py-16 dark:border-neutral-700">
                    <flux:icon.magnifying-glass class="size-12 text-neutral-400" />
                    <flux:heading size="lg" class="mt-4">No products found</flux:heading>
                    <flux:text class="mt-2 text-neutral-600 dark:text-neutral-400">
                        Try adjusting your search or filters
                    </flux:text>
                </div>
                @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($products as $product)
                    <a href="{{ route('products.show', $product) }}" wire:key="product-{{ $product->id }}"
                        class="group flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-4 transition hover:shadow-lg dark:border-neutral-700 dark:bg-neutral-800">
                        <div>
                            <flux:heading size="lg"
                                class="line-clamp-2 transition group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                {{ $product->name }}
                            </flux:heading>
                        </div>

                        @if($product->ean)
                        <div>
                            <flux:text class="text-xs text-neutral-500">EAN</flux:text>
                            <flux:text class="font-mono text-sm">{{ $product->ean }}</flux:text>
                        </div>
                        @endif

                        @if($product->manufacturer)
                        <div>
                            <flux:text class="text-xs text-neutral-500">Manufacturer</flux:text>
                            <flux:text class="text-sm">{{ $product->manufacturer->name }}</flux:text>
                        </div>
                        @endif

                        @if($product->items_count > 0)
                        <div class="mt-auto">
                            <flux:badge size="sm" variant="outline">
                                {{ $product->items_count }} {{ Str::plural('distributor', $product->items_count) }}
                            </flux:badge>
                        </div>
                        @endif
                    </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
                @endif
            </div>

            {{-- Loading State --}}
            <div wire:loading wire:target="search,selectedManufacturers,selectedDistributors"
                class="flex items-center justify-center py-16">
                <x-filament::loading-indicator class="size-10 text-primary-500" />
            </div>
        </div>
    </div>
</div>
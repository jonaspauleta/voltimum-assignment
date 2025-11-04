<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Search Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" class="font-semibold text-neutral-900 dark:text-neutral-100">Products</flux:heading>
            <flux:text class="mt-1 text-neutral-600 dark:text-neutral-400">
                Search and browse our product catalog
            </flux:text>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="w-full max-w-2xl">
        <flux:input wire:model.live.debounce.300ms="search" type="search"
            placeholder="Search by name, EAN, manufacturer, SKU..." icon="magnifying-glass" class="w-full" />
    </div>

    {{-- Results Info --}}
    @if($search)
    <div class="flex items-center gap-2">
        <flux:badge variant="outline" class="dark:border-neutral-600 dark:text-neutral-300">
            {{ $this->products->total() }} {{ Str::plural('result', $this->products->total()) }} for "{{ $search }}"
        </flux:badge>
        <flux:button wire:click="clearSearch" variant="ghost" size="sm" icon="x-mark">
            Clear search
        </flux:button>
    </div>
    @endif

    {{-- Products Grid --}}
    @if($this->products->isEmpty())
    <div
        class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-neutral-300 bg-white py-16 dark:border-neutral-700 dark:bg-neutral-900">
        <div class="text-center">
            <flux:icon.magnifying-glass class="mx-auto size-12 text-neutral-400 dark:text-neutral-600" />
            <flux:heading size="lg" class="mt-4 text-neutral-900 dark:text-neutral-100">No products found</flux:heading>
            <flux:text class="mt-2 text-neutral-600 dark:text-neutral-400">
                @if($search)
                Try adjusting your search terms
                @else
                No products are currently available
                @endif
            </flux:text>
        </div>
    </div>
    @else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($this->products as $product)
        <a href="{{ route('products.show', $product) }}" wire:key="product-{{ $product->id }}"
            class="group relative flex flex-col gap-3 overflow-hidden rounded-xl border border-neutral-200 bg-white p-4 transition hover:shadow-lg dark:border-neutral-700 dark:bg-neutral-800 dark:hover:shadow-xl dark:hover:shadow-neutral-900/50">
            <div>
                <flux:heading size="lg"
                    class="line-clamp-2 text-neutral-900 transition group-hover:text-blue-600 dark:text-neutral-100 dark:group-hover:text-blue-400">
                    {{ $product->name }}
                </flux:heading>
                <flux:text class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {{ $product->manufacturer->name }}
                </flux:text>
            </div>

            <flux:text class="line-clamp-3 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $product->description }}
            </flux:text>

            <div class="mt-auto flex flex-wrap gap-2">
                <flux:badge variant="outline" size="sm"
                    class="border-neutral-300 text-neutral-700 dark:border-neutral-600 dark:text-neutral-300">
                    EAN: {{ $product->ean }}
                </flux:badge>
                @if($product->items_count > 0)
                <flux:badge variant="outline" size="sm"
                    class="border-neutral-300 text-neutral-700 dark:border-neutral-600 dark:text-neutral-300">
                    {{ $product->items_count }} {{ Str::plural('distributor', $product->items_count) }}
                </flux:badge>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $this->products->links() }}
    </div>
    @endif
</div>
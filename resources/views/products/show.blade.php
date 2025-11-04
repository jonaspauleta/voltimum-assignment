<x-layouts.app :title="$product->name">
  <div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Breadcrumbs --}}
    <flux:breadcrumbs>
      <flux:breadcrumbs.item href="{{ route('products.index') }}" icon="cube">Products</flux:breadcrumbs.item>
      <flux:breadcrumbs.item>{{ $product->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Product Details Section --}}
    <div class="flex flex-col gap-6">
      <div>
        <div class="flex items-start justify-between gap-4">
          <flux:heading size="2xl" class="font-bold text-neutral-900 dark:text-neutral-100">
            {{ $product->name }}
          </flux:heading>
        </div>

        <flux:text class="mt-2 text-lg text-neutral-600 dark:text-neutral-400">
          {{ $product->description }}
        </flux:text>
      </div>

      <flux:separator />

      {{-- Product Information --}}
      <div class="grid gap-4">
        <flux:heading size="lg" class="text-neutral-900 dark:text-neutral-100">Product Information</flux:heading>

        <div class="grid gap-3">
          <div class="flex justify-between">
            <flux:text class="font-medium text-neutral-900 dark:text-neutral-100">EAN</flux:text>
            <flux:text class="font-mono text-neutral-600 dark:text-neutral-400">{{ $product->ean }}</flux:text>
          </div>

          <div class="flex justify-between">
            <flux:text class="font-medium text-neutral-900 dark:text-neutral-100">Slug</flux:text>
            <flux:text class="font-mono text-neutral-600 dark:text-neutral-400">{{ $product->slug }}</flux:text>
          </div>

          <div class="flex justify-between">
            <flux:text class="font-medium text-neutral-900 dark:text-neutral-100">Manufacturer</flux:text>
            <flux:text class="text-neutral-600 dark:text-neutral-400">{{ $product->manufacturer->name }}</flux:text>
          </div>
        </div>
      </div>

      @if($product->items->isNotEmpty())
      <flux:separator />

      {{-- Distributor Information --}}
      <div class="grid gap-4">
        <flux:heading size="lg" class="text-neutral-900 dark:text-neutral-100">
          Available from {{ $product->items->count() }} {{ Str::plural('Distributor', $product->items->count()) }}
        </flux:heading>

        <div class="grid gap-3">
          @foreach($product->items as $item)
          <div
            class="rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <flux:text class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $item->distributor->name }}
                </flux:text>
                <div class="mt-2 grid gap-2 text-sm">
                  <div class="flex justify-between">
                    <flux:text class="text-neutral-600 dark:text-neutral-400">SKU</flux:text>
                    <flux:text class="font-mono text-neutral-900 dark:text-neutral-100">{{ $item->sku }}</flux:text>
                  </div>
                  <div class="flex justify-between">
                    <flux:text class="text-neutral-600 dark:text-neutral-400">Price</flux:text>
                    <flux:text class="font-semibold text-neutral-900 dark:text-neutral-100">${{
                      number_format($item->price, 2) }}</flux:text>
                  </div>
                  @if(isset($item->available))
                  <div class="flex justify-between">
                    <flux:text class="text-neutral-600 dark:text-neutral-400">Availability</flux:text>
                    @if($item->available)
                    <flux:badge variant="solid" size="sm"
                      class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      In Stock
                    </flux:badge>
                    @else
                    <flux:badge variant="outline" size="sm"
                      class="border-neutral-300 text-neutral-700 dark:border-neutral-600 dark:text-neutral-400">
                      Out of Stock
                    </flux:badge>
                    @endif
                  </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @else
      <flux:separator />

      <flux:callout variant="warning"
        class="border-yellow-300 bg-yellow-50 text-yellow-900 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200">
        This product is not currently available from any distributors.
      </flux:callout>
      @endif

      {{-- Actions --}}
      <div class="mt-auto flex gap-3">
        <flux:button href="{{ route('products.index') }}" variant="outline" class="flex-1">
          <flux:icon.arrow-left />
          Back to Products
        </flux:button>
      </div>
    </div>
  </div>
</x-layouts.app>
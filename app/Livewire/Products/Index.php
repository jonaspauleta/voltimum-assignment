<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        if ($this->search === '' || $this->search === '0') {
            /** @var LengthAwarePaginator<int, Product> */
            return Product::with(['manufacturer', 'items.distributor'])
                ->withCount('items')
                ->latest()
                ->paginate(12);
        }

        /** @var LengthAwarePaginator<int, Product> */
        return Product::search($this->search)
            ->query(fn ($query) => $query->with(['manufacturer', 'items.distributor'])->withCount('items'))
            ->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.products.index');
    }
}

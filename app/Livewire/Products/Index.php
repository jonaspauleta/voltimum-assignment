<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

final class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'manufacturers', history: true)]
    /** @var array<int, string> */
    public array $selectedManufacturers = [];

    #[Url(as: 'distributors', history: true)]
    /** @var array<int, string> */
    public array $selectedDistributors = [];

    public int $perPage = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedManufacturers(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDistributors(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedManufacturers = [];
        $this->selectedDistributors = [];
        $this->resetPage();
    }

    public function clearAll(): void
    {
        $this->search = '';
        $this->selectedManufacturers = [];
        $this->selectedDistributors = [];
        $this->resetPage();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || ! empty($this->selectedManufacturers)
            || ! empty($this->selectedDistributors);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        // Always perform search with Scout + Typesense to get facets
        $builder = $this->buildSearchQuery();

        /** @var LengthAwarePaginator<int, Product> */
        $results = $builder
            ->query(fn ($query) => $query
                ->with(['manufacturer', 'items.distributor'])
                ->withCount('items')
            )
            ->paginate($this->perPage);

        return $results;
    }

    /**
     * @return array<string, array<string, int>>
     */
    #[Computed]
    public function facets(): array
    {
        try {
            // Always fetch ALL facets without filters, so they remain stable
            $query = $this->search !== '' ? $this->search : '*';

            $builder = Product::search($query)->options([
                'facet_by' => 'manufacturer_name,distributor_names',
                'max_facet_values' => 100,
            ]);

            $raw = $builder->raw();

            $facets = [];

            if (! isset($raw['facet_counts']) || ! is_array($raw['facet_counts'])) {
                return [];
            }

            foreach ($raw['facet_counts'] as $facetCount) {
                if (! $this->isValidFacetCount($facetCount)) {
                    continue;
                }

                $fieldName = $facetCount['field_name'];
                $facets[$fieldName] = [];

                foreach ($facetCount['counts'] as $count) {
                    if ($this->isValidCount($count)) {
                        $facets[$fieldName][$count['value']] = $count['count'];
                    }
                }

                // Sort facets by count (descending)
                arsort($facets[$fieldName]);
            }

            return $facets;
        } catch (Throwable) {
            return [];
        }
    }

    public function render(): View
    {
        return view('livewire.products.index');
    }

    private function buildSearchQuery(): Builder
    {
        // Use wildcard search if no specific search term
        $query = $this->search !== '' ? $this->search : '*';

        $builder = Product::search($query);

        // Build Typesense filter expressions
        $filters = $this->buildFilters();

        $options = [
            'facet_by' => 'manufacturer_name,distributor_names',
            'max_facet_values' => 100,
            'per_page' => $this->perPage,
        ];

        if (! empty($filters)) {
            $options['filter_by'] = implode(' && ', $filters);
        }

        return $builder->options($options);
    }

    /**
     * @return array<int, string>
     */
    private function buildFilters(): array
    {
        $filters = [];

        // Add manufacturer filters
        if (! empty($this->selectedManufacturers)) {
            $manufacturerConditions = array_map(
                fn (string $name): string => "manufacturer_name:=`{$name}`",
                $this->selectedManufacturers
            );
            $filters[] = '('.implode(' || ', $manufacturerConditions).')';
        }

        // Add distributor filters
        if (! empty($this->selectedDistributors)) {
            $distributorConditions = array_map(
                fn (string $name): string => "distributor_names::`{$name}`",
                $this->selectedDistributors
            );
            $filters[] = '('.implode(' || ', $distributorConditions).')';
        }

        return $filters;
    }

    private function isValidFacetCount(mixed $facetCount): bool
    {
        return is_array($facetCount)
            && isset($facetCount['field_name'])
            && isset($facetCount['counts'])
            && is_array($facetCount['counts']);
    }

    private function isValidCount(mixed $count): bool
    {
        return is_array($count)
            && isset($count['value'])
            && isset($count['count'])
            && is_int($count['count']);
    }
}

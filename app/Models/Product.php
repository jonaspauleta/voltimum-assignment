<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $manufacturer_id
 * @property string $name
 * @property string $slug
 * @property string $ean
 * @property string $description
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property Manufacturer $manufacturer
 * @property Collection<int, Item> $items
 */
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasSlug, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'manufacturer_id',
        'name',
        'slug',
        'ean',
        'description',
    ];

    protected $with = ['manufacturer', 'items.distributor'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Manufacturer, $this>
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'ean' => $this->ean,
            'description' => $this->description,
            'manufacturer' => [
                'id' => (string) $this->manufacturer->id,
                'name' => $this->manufacturer->name,
                'slug' => $this->manufacturer->slug,
            ],
            'items' => $this->items->map(fn (Item $item): array => [
                'id' => (string) $item->id,
                'sku' => $item->sku,
                'distributor' => [
                    'id' => (string) $item->distributor->id,
                    'name' => $item->distributor->name,
                    'slug' => $item->distributor->slug,
                ],
            ])->values()->all(),
        ];
    }

    public function searchableAs(): string
    {
        return 'products';
    }

    public function getScoutKeyName(): string
    {
        return 'id';
    }
}

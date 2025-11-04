<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
class Distributor extends Model
{
    /** @use HasFactory<\Database\Factories\DistributorFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}

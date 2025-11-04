<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

final class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index');
    }

    public function show(Product $product): View
    {
        $product->load(['manufacturer', 'items.distributor']);

        return view('products.show', ['product' => $product]);
    }
}

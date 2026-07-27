<?php

namespace App\Http\Controllers;

use App\Models\Category;

class StorefrontController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['products' => function ($q) {
                $q->where('is_available', true)->with(['vendor', 'category']);
            }])
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        return view('storefront.index', compact('categories'));
    }

    public function category(Category $category)
    {
        $products = $category->products()
            ->where('is_available', true)
            ->with(['vendor', 'category'])
            ->get();

        return view('storefront.category', compact('category', 'products'));
    }
}

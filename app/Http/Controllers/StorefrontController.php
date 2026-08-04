<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Vendor;

class StorefrontController extends Controller
{
    public function index()
    {
        $vendors = Vendor::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->filter(fn ($v) => $v->products_count > 0);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('products')
            ->get()
            ->filter(fn ($c) => $c->products_count > 0);

        return view('storefront.index', compact('vendors', 'categories'));
    }

    public function category(Category $category)
    {
        $query = $category->products()
            ->where('is_available', true)
            ->with(['vendor', 'category']);

        if ($q = request()->string('q')->trim()->lower()) {
            $query->where(function ($b) use ($q) {
                $b->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(name_ar) LIKE ?', ["%{$q}%"]);
            });
        }

        if ($vendor = request()->integer('vendor')) {
            $query->where('vendor_id', $vendor);
        }

        $products = $query->orderBy('name')->paginate(48)->withQueryString();
        $vendors = $category->products()
            ->where('is_available', true)
            ->with('vendor')
            ->get()
            ->pluck('vendor')
            ->filter()
            ->unique('id')
            ->sortBy('name');

        return view('storefront.category', compact('category', 'products', 'vendors'));
    }

    public function vendor(Vendor $vendor)
    {
        $groups = $vendor->products()
            ->where('is_available', true)
            ->with('category')
            ->get()
            ->groupBy(fn ($p) => $p->category_id ?? 0)
            ->sortKeys();

        return view('storefront.vendor', compact('vendor', 'groups'));
    }
}

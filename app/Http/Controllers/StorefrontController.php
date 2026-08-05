<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;

class StorefrontController extends Controller
{
    public function index()
    {
        $vendors = Vendor::query()
            ->where('is_active', true)
            ->with('category.parent')
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->filter(fn ($v) => $v->products_count > 0)
            ->values();

        // Resolve every vendor to its TOP-LEVEL category, so a vendor filed
        // under "Fast food" still shows up beneath "Restaurants".
        $vendorsByRoot = $vendors->groupBy(
            fn ($v) => optional($v->category?->parent ?? $v->category)->id ?? 0
        );

        // Only surface sections that actually exist in the admin: a top-level
        // category is shown when it has vendors or products of its own.
        $sections = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('products')
            ->get()
            ->map(function ($category) use ($vendorsByRoot) {
                $categoryVendors = $vendorsByRoot->get($category->id, collect());

                return (object) [
                    'category'   => $category,
                    'vendors'    => $categoryVendors,
                    'open'       => $categoryVendors->filter->is_open->count(),
                    // Products sold directly by the category (universal catalog).
                    'products'   => $category->products_count,
                ];
            })
            ->filter(fn ($s) => $s->vendors->isNotEmpty() || $s->products > 0)
            ->values();

        // Flat index powering the instant "find any product in any store" search.
        $productIndex = Product::query()
            ->where('is_available', true)
            ->with(['vendor', 'category'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'name_ar'   => $p->name_ar ?: $p->name,
                'price'     => (float) $p->price,
                'vendor_id' => $p->vendor_id,
                'vendor'    => $p->vendor?->name,
                'vendor_ar' => $p->vendor?->name_ar ?: $p->vendor?->name,
                'slug'      => $p->vendor?->slug,
                'is_open'   => $p->vendor ? $p->vendor->is_open : true,
                'icon'      => $p->category?->icon ?: '🛍️',
                'q'         => mb_strtolower(trim(
                    $p->name.' '.$p->name_ar.' '.$p->vendor?->name.' '.$p->vendor?->name_ar
                )),
            ])
            ->values();

        return view('storefront.index', compact('vendors', 'sections', 'productIndex'));
    }

    /**
     * A category opens as its own page. It lists the stores that belong to the
     * category (including any sub-categories), and — for categories sold from a
     * shared catalog rather than a specific shop — the products themselves.
     */
    public function category(Category $category)
    {
        $childIds = $category->children()->pluck('id')->push($category->id);

        // Stores filed under this category or any of its children.
        $storeList = Vendor::query()
            ->where('is_active', true)
            ->whereIn('category_id', $childIds)
            ->with('category')
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->filter(fn ($v) => $v->products_count > 0)
            ->values();

        // Universal-catalog products: sold by the category itself, not a store.
        $query = $category->products()
            ->where('is_available', true)
            ->whereNull('vendor_id')
            ->with('category');

        if ($q = request()->string('q')->trim()->lower()) {
            $query->where(function ($b) use ($q) {
                $b->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(name_ar) LIKE ?', ["%{$q}%"]);
            });
        }

        $products = $query->orderBy('name')->paginate(48)->withQueryString();

        return view('storefront.category', [
            'category'  => $category,
            'storeList' => $storeList,
            'products'  => $products,
        ]);
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

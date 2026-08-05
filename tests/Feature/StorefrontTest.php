<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    /** An empty catalogue must still render, not 500. */
    public function test_home_page_renders_with_no_data(): void
    {
        $this->get('/')->assertSuccessful();
    }

    public function test_home_page_lists_top_level_categories_only(): void
    {
        $restaurants = Category::create([
            'name' => 'Restaurants', 'slug' => 'restaurants', 'icon' => '🍔', 'is_active' => true,
        ]);
        $grills = Category::create([
            'name' => 'Grills', 'slug' => 'grills', 'parent_id' => $restaurants->id, 'is_active' => true,
        ]);

        $vendor = Vendor::create([
            'name' => 'Test Grill', 'slug' => 'test-grill',
            'category_id' => $grills->id, 'is_active' => true,
        ]);
        Product::create([
            'name' => 'Shish Tawouk', 'price' => 9.5,
            'category_id' => $grills->id, 'vendor_id' => $vendor->id, 'is_available' => true,
        ]);

        // Only the parent gets a tile. The section still appears in the page's
        // search index — that's how searching "grills" finds the vendor — so
        // assert on the tile link rather than the word itself.
        $this->get('/')
            ->assertSuccessful()
            ->assertSee(route('storefront.category', $restaurants))
            ->assertDontSee(route('storefront.category', $grills));
    }

    public function test_vendor_page_shows_its_menu(): void
    {
        $category = Category::create(['name' => 'Restaurants', 'slug' => 'restaurants', 'is_active' => true]);
        $vendor = Vendor::create([
            'name' => 'Test Grill', 'slug' => 'test-grill',
            'category_id' => $category->id, 'is_active' => true,
        ]);
        Product::create([
            'name' => 'Shish Tawouk', 'price' => 9.5,
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'is_available' => true,
        ]);

        $this->get('/vendor/test-grill')
            ->assertSuccessful()
            ->assertSee('Shish Tawouk');
    }

    /** A vendor with no opening hours configured is treated as open. */
    public function test_vendor_without_hours_is_open(): void
    {
        $vendor = Vendor::create(['name' => 'X', 'slug' => 'x', 'is_active' => true]);

        $this->assertTrue($vendor->is_open);
    }
}

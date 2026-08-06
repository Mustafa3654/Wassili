<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Imports universal-catalog products (e.g. supermarket items) from JSON:
 *
 * {
 *   "category": { "slug": "supermarket", "name": "Supermarket", "name_ar": "سوبر ماركت" },
 *   "items":    [ { "name": "...", "name_ar": "...", "price": 3.25 } ]
 * }
 *
 * Products are created without a vendor (vendor_id = NULL) so they land in
 * the shared Universal Catalog, not under a specific restaurant.
 */
class ImportUniversal extends Command
{
    protected $signature = 'wassili:import-universal {file} {--fresh : Delete existing universal-catalog products before importing}';

    protected $description = 'Import universal catalog items (e.g. supermarket products) from JSON';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        ini_set('memory_limit', '512M');

        $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($path)), true);

        if (! is_array($data)) {
            $this->error('Invalid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }

        DB::transaction(function () use ($data) {
            $cat = $data['category'] ?? [];

            $category = Category::firstOrCreate(
                ['slug' => $cat['slug'] ?? 'supermarket'],
                [
                    'name'       => $cat['name'] ?? 'Supermarket',
                    'name_ar'    => $cat['name_ar'] ?? 'سوبر ماركت',
                    'icon'       => $cat['icon'] ?? '🛒',
                    'parent_id'  => null,
                    'sort_order' => 0,
                    'is_active'  => true,
                ]
            );

            if ($this->option('fresh')) {
                $deleted = Product::whereNull('vendor_id')->delete();
                $this->info("Deleted {$deleted} existing universal products.");
            }

            $rows = [];
            foreach ($data['items'] as $item) {
                $rows[] = [
                    'name'         => $item['name'],
                    'name_ar'      => $item['name_ar'] ?? $item['name'],
                    'price'        => (float) ($item['price'] ?? 0),
                    'category_id'  => $category->id,
                    'vendor_id'    => null,
                    'is_available' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                Product::insert($chunk);
            }

            $this->info("Category: {$category->name} (#{$category->id})");
            $this->info('Items: '.count($rows));
        });

        return self::SUCCESS;
    }
}

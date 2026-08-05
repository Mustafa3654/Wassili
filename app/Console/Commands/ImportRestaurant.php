<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports one restaurant from a JSON file:
 *
 * {
 *   "vendor":   { "name", "name_ar", "slug", "phone", "address", "parent": "restaurants",
 *                 "hours": { "open": "09:00", "close": "23:00", "days": ["monday", ...] } },
 *   "sections": { "Breakfast": {"ar": "فطور"}, ... },
 *   "items":    [ { "section": "Breakfast", "name": "Foul", "name_ar": "فول", "price": 3.98 } ]
 * }
 *
 * Menu sections become sub-categories of the parent category and are shared
 * between restaurants, so "Grills" is one category no matter who serves it.
 */
class ImportRestaurant extends Command
{
    protected $signature = 'wassili:import-restaurant {file}';

    protected $description = 'Import a single restaurant (vendor + menu sections + items) from JSON';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($path)), true);

        if (! is_array($data)) {
            $this->error('Invalid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }

        DB::transaction(function () use ($data) {
            $v = $data['vendor'];

            $parent = Category::where('slug', $v['parent'] ?? 'restaurants')->firstOrFail();

            // ---- menu sections -> shared sub-categories ----
            $sectionIds = [];
            $order = 0;
            foreach (($data['sections'] ?? []) as $name => $meta) {
                $category = Category::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name'       => $name,
                        'name_ar'    => $meta['ar'] ?? $name,
                        'icon'       => $meta['icon'] ?? null,
                        'parent_id'  => $parent->id,
                        'sort_order' => $order++,
                        'is_active'  => true,
                    ]
                );
                $sectionIds[$name] = $category->id;
            }

            // ---- vendor ----
            $hours = $data['vendor']['hours'] ?? null;
            $schedule = null;

            if ($hours) {
                $days = $hours['days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $schedule = [];
                foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                    $schedule[$day] = [
                        'is_open' => in_array($day, $days, true),
                        'open'    => $hours['open'] ?? '09:00',
                        'close'   => $hours['close'] ?? '23:00',
                    ];
                }
            }

            $vendor = Vendor::updateOrCreate(
                ['slug' => $v['slug']],
                [
                    'name'          => $v['name'],
                    'name_ar'       => $v['name_ar'] ?? $v['name'],
                    'category_id'   => $parent->id,
                    'phone'         => $v['phone'] ?? null,
                    'address'       => $v['address'] ?? null,
                    'opening_hours' => $schedule,
                    'is_active'     => true,
                ]
            );

            // Replace this vendor's menu so re-running is idempotent.
            Product::where('vendor_id', $vendor->id)->delete();

            $rows = [];
            foreach ($data['items'] as $item) {
                $rows[] = [
                    'name'         => $item['name'],
                    'name_ar'      => $item['name_ar'] ?? $item['name'],
                    'price'        => (float) ($item['price'] ?? 0),
                    'category_id'  => $sectionIds[$item['section']] ?? $parent->id,
                    'vendor_id'    => $vendor->id,
                    'is_available' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                Product::insert($chunk);
            }

            $this->info("Vendor: {$vendor->name} (#{$vendor->id})");
            $this->info('Sections: '.count($sectionIds));
            $this->info('Items: '.count($rows));
        });

        return self::SUCCESS;
    }
}

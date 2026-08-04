<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMenus extends Command
{
    protected $signature = 'wassili:import-menus {file : Path to wassili-menus.json}';

    protected $description = 'Import categories, vendors and products from wassili-menus.json';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($path)), true);
        if (! is_array($data)) {
            $this->error('Invalid JSON');
            return self::FAILURE;
        }

        DB::transaction(function () use ($data) {
            $slugToId = [];

            foreach ($data['categories'] as $i => $cat) {
                $category = Category::updateOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name'       => $cat['name'],
                        'name_ar'    => $cat['name_ar'],
                        'parent_id'  => null,
                        'sort_order' => $i,
                        'is_active'  => true,
                    ]
                );
                $slugToId[$cat['slug']] = $category->id;
            }
            $this->info('Categories: '.count($data['categories']));

            $vendorIds = [];
            foreach ($data['vendors'] as $v) {
                $vendor = Vendor::updateOrCreate(
                    ['slug' => $v['slug']],
                    [
                        'name'      => $v['name'],
                        'name_ar'   => $v['name_ar'],
                        'is_active' => true,
                    ]
                );
                $vendorIds[$v['key']] = $vendor->id;
            }
            $this->info('Vendors: '.count($data['vendors']));

            $created = 0;
            $skipped = 0;
            foreach ($data['products'] as $p) {
                if (! isset($vendorIds[$p['vendor']]) || ! isset($slugToId[$p['category']])) {
                    $skipped++;
                    continue;
                }

                Product::updateOrCreate(
                    [
                        'vendor_id'  => $vendorIds[$p['vendor']],
                        'name'       => $p['name'],
                        'category_id'=> $slugToId[$p['category']],
                    ],
                    [
                        'name_ar'      => $p['name_ar'] ?? null,
                        'description'  => $p['desc'] ?? null,
                        'description_ar'=> null,
                        'price'        => $p['price'],
                        'is_available' => true,
                    ]
                );
                $created++;
            }
            $this->info("Products: {$created} created/updated, {$skipped} skipped");
        });

        return self::SUCCESS;
    }
}

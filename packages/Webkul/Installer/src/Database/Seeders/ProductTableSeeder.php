<?php

namespace Webkul\Installer\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;
use Webkul\Core\Helpers\Database\DatabaseSequenceHelper;

class ProductTableSeeder extends Seeder
{
    public function run(array $parameters = []): void
    {
        DB::table('products')->delete();

        $now = Carbon::now();

        $jsonPath = __DIR__ . '/../Data/products.json';

        if (! File::exists($jsonPath)) {
            $this->command?->error('products.json file not found.');

            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        if (! isset($data['products'])) {
            $this->command?->error('Invalid JSON format.');

            return;
        }

        $products = [];

        foreach ($data['products'] as $product) {
            try {
                $values = $product['values'];

                $products[] = [
                    'sku'                 => $product['sku'],
                    'type'                => $product['type'] ?? 'simple',
                    'status'              => 1,
                    'attribute_family_id' => $product['attribute_family_id'] ?? 1,
                    'values'              => json_encode($values),
                    'additional'          => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            } catch (Throwable $e) {
                $this->command?->error('Failed to process product: ' . ($product['sku'] ?? 'unknown') . ' - ' . $e->getMessage());
            }
        }

        try {
            DB::table('products')->insert($products);

            $this->command?->info('Products imported successfully.');
        } catch (Throwable $e) {
            $this->command?->error('Failed to insert products: ' . $e->getMessage());
        }

        DatabaseSequenceHelper::fixSequences(['products']);
    }
}
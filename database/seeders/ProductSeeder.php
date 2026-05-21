<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = config('site.products', []);

        foreach ($catalog as $item) {
            Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => ($item['description'] ?? $item['short'] ?? '')."\n\n".implode("\n", array_map(fn ($f) => '• '.$f, $item['features'] ?? [])),
                    'price' => $item['price'],
                    'discount_price' => null,
                    'stock_quantity' => 100,
                    'status' => Product::STATUS_ACTIVE,
                ],
            );
        }

        if (Product::query()->doesntExist()) {
            Product::create([
                'name' => 'Premium Soy Flour — 1kg',
                'slug' => 'premium-soy-flour-1kg',
                'description' => 'Fine-milled soy flour for baking and cooking.',
                'price' => 3500,
                'stock_quantity' => 50,
                'status' => Product::STATUS_ACTIVE,
            ]);
        }
    }
}

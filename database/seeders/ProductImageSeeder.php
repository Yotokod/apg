<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $file = __DIR__.'/data/product_images.json';
        if (! is_file($file)) {
            return;
        }
        foreach (json_decode(file_get_contents($file), true) ?: [] as $slug => $path) {
            if (is_file(storage_path('app/public/'.$path))) {
                Product::where('slug', $slug)->whereNull('image')->update(['image' => $path]);
            }
        }
    }
}

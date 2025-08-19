<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing categories
        $categories = Category::all();

        // if ($categories->isEmpty()) {
        //     $this->call(CategorySeeder::class);
        //     $categories = Category::all();
        // }

        // Create sample products for each category
        $sampleProducts = [
            // Makanan
            [
                'name' => 'Nasi Goreng',
                'description' => 'Nasi goreng spesial dengan telur dan ayam',
                'price' => 25000,
                'current_stock' => 50,
                'minimum_stock' => 10,
                'category_name' => 'Makanan',
            ],
            [
                'name' => 'Mie Ayam',
                'description' => 'Mie ayam dengan topping lengkap',
                'price' => 20000,
                'current_stock' => 30,
                'minimum_stock' => 5,
                'category_name' => 'Makanan',
            ],

            // Minuman
            [
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'price' => 5000,
                'current_stock' => 100,
                'minimum_stock' => 20,
                'category_name' => 'Minuman',
            ],
            [
                'name' => 'Kopi Hitam',
                'description' => 'Kopi hitam panas premium',
                'price' => 8000,
                'current_stock' => 80,
                'minimum_stock' => 15,
                'category_name' => 'Minuman',
            ],

            // Kebutuhan Sehari-hari
            [
                'name' => 'Sabun Mandi',
                'description' => 'Sabun mandi cair antibakteri',
                'price' => 15000,
                'current_stock' => 25,
                'minimum_stock' => 5,
                'category_name' => 'Kebutuhan Sehari-hari',
            ],
            [
                'name' => 'Pasta Gigi',
                'description' => 'Pasta gigi untuk gigi sehat',
                'price' => 12000,
                'current_stock' => 40,
                'minimum_stock' => 10,
                'category_name' => 'Kebutuhan Sehari-hari',
            ],
        ];

        foreach ($sampleProducts as $productData) {
            $category = $categories->where('name', $productData['category_name'])->first();

            if ($category) {
                unset($productData['category_name']);
                $productData['category_id'] = $category->id;
                Product::create($productData);
            }
        }

        // Create additional random products
        // Product::factory(30)->create();

        // Create some products with specific states for testing
        // Product::factory(5)->outOfStock()->create();
        // Product::factory(3)->lowStock()->create();
        // Product::factory(2)->inactive()->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Kategori untuk semua jenis makanan dan snack',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Kategori untuk semua jenis minuman',
            ],
            [
                'name' => 'Elektronik',
                'description' => 'Kategori untuk perangkat elektronik dan aksesoris',
            ],
            [
                'name' => 'Kebutuhan Sehari-hari',
                'description' => 'Kategori untuk kebutuhan rumah tangga dan personal',
            ],
            [
                'name' => 'Pakaian',
                'description' => 'Kategori untuk semua jenis pakaian dan aksesoris fashion',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create additional random categories for testing
        // Category::factory(5)->create();
    }
}

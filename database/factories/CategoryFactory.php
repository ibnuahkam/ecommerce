<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition()
    {
        $categories = [
            'Smartphone',
            'Fashion Pria',
            'Fashion Wanita',
            'Elektronik',
            'Home & Living',
            'Kesehatan & Kecantikan',
            'Olahraga & Outdoor',
            'Otomotif',
            'Mainan & Hobi',
            'Makanan & Minuman',
            'Buku & Alat Tulis',
            'Perlengkapan Bayi',
            'Perlengkapan Rumah Tangga',
        ];

        $name = $this->faker->unique()->randomElement($categories);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}

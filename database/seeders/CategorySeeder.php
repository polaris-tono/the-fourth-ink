<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['National', 'national', 'News from Bangladesh.'],
            ['International', 'international', 'News from around the world.'],
            ['Technology', 'technology', 'Technology and science news.'],
            ['Sports', 'sports', 'Sports news and results.'],
            ['Business', 'business', 'Business and economy.'],
            ['Entertainment', 'entertainment', 'Film, music, and culture.'],
            ['Campus', 'campus', 'News from our campus.'],
        ];

        foreach ($categories as $i => [$name, $slug, $description]) {
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $i + 1]
            );
        }
    }
}
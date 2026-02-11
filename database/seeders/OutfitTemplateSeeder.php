<?php

namespace Database\Seeders;

use App\Models\OutfitTemplate;
use Illuminate\Database\Seeder;

class OutfitTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Business Casual',
                'occasion' => 'work',
                'description' => 'Professional yet comfortable outfit for the office',
                'slots' => [
                    ['label' => 'Top', 'category' => 'upper', 'required' => true],
                    ['label' => 'Bottom', 'category' => 'lower', 'required' => true],
                ],
                'icon' => 'briefcase',
                'is_system' => true,
            ],
            [
                'name' => 'Date Night',
                'occasion' => 'date',
                'description' => 'Stylish outfit for a special evening',
                'slots' => [
                    ['label' => 'Outfit', 'category' => 'dress', 'required' => true],
                ],
                'icon' => 'heart',
                'is_system' => true,
            ],
            [
                'name' => 'Weekend Casual',
                'occasion' => 'casual',
                'description' => 'Relaxed and comfortable weekend look',
                'slots' => [
                    ['label' => 'Top', 'category' => 'upper', 'required' => true],
                    ['label' => 'Bottom', 'category' => 'lower', 'required' => true],
                ],
                'icon' => 'sun',
                'is_system' => true,
            ],
            [
                'name' => 'Formal Event',
                'occasion' => 'formal',
                'description' => 'Elegant outfit for formal occasions',
                'slots' => [
                    ['label' => 'Top', 'category' => 'upper', 'required' => true],
                    ['label' => 'Bottom', 'category' => 'lower', 'required' => true],
                ],
                'icon' => 'star',
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            OutfitTemplate::firstOrCreate(
                ['name' => $template['name'], 'is_system' => true],
                $template
            );
        }
    }
}

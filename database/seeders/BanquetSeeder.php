<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banquet;
use App\Models\BanquetImage;
use Illuminate\Support\Str;

class BanquetSeeder extends Seeder
{
    public function run(): void
    {
        $banquets = [
            'Onex Banquet',
            'Sapphire Banquet',
        ];
        foreach ($banquets as $index => $title) {
            $banquet = Banquet::create([
                'title'=> $title,
            ]);
        }
    }
}

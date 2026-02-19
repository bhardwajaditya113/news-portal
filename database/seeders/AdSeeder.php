<?php

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ad::updateOrCreate(
            ['id' => 1],
            [
                'home_top_bar_ad' => null,
                'home_middle_ad' => null,
                'view_page_ad' => null,
                'news_page_ad' => null,
                'side_bar_ad' => null,
                'home_top_bar_ad_status' => 0,
                'home_middle_ad_status' => 0,
                'view_page_ad_status' => 0,
                'news_page_ad_status' => 0,
                'side_bar_ad_status' => 0,
                'home_top_bar_ad_url' => null,
                'home_middle_ad_url' => null,
                'view_page_ad_url' => null,
                'news_page_ad_url' => null,
                'side_bar_ad_url' => null,

            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['value' => 2020],
            ['value' => 2019],
            ['value' => 2018],
            ['value' => 2021],
            ['value' => 2022],
        ];

        foreach ($years as $year) {
            Year::updateOrCreate(['value' => $year['value']], $year);
        }
    }
}

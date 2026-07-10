<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Year;

class YearFactory extends Factory
{
    protected $model = Year::class;

    public function definition(): array
    {
        return [
            'value' => fake()->year(),
        ];
    }
}

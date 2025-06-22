<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->words(2, true), // ex: "Adubo Orgânico"
            'tipo' => $this->faker->randomElement(['fertilizante', 'defensivo', 'semente']),
            'unidade' => $this->faker->randomElement(['kg', 'lt', 'un']),
            'estoque_minimo' => $this->faker->randomFloat(2, 5, 20),
            'estoque_atual' => $this->faker->randomFloat(2, 10, 200),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}


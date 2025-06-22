<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Produto;

class MovimentacaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'produto_id' => Produto::inRandomOrder()->first()->id, // pega ID real
            'tipo' => $this->faker->randomElement(['entrada', 'saida']),
            'quantidade' => $this->faker->randomFloat(2, 1, 50),
            'data_movimento' => $this->faker->date(),
            'observacao' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movimentacao;

class MovimentacaoSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Movimentacao::factory()->count(50)->create(); // 50 movimentações
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Produto extends Model
{
    use HasFactory;
    
    // Busca todos os produtos do banco e ordena pelo nome (A-Z) 
    public static function getAll()
    {
        return DB::table('produtos')
            ->select('*')
            ->orderBy('nome', 'asc')
            ->get();
    }

    // Busca os produtos de um tipo específico e ordena pelo nome (A-Z)
    public static function getByTipo($tipo)
    {
        return DB::table('produtos')
            ->select('*')
            ->where('tipo', $tipo)
            ->orderBy('nome', 'asc')
            ->get();
    }

    // Busca produtos com estoque abaixo do mínimo e ordena pelo nome (A-Z)
    public static function getEstoqueBaixo()
    {
        return DB::table('produtos')
            ->select('id', 'nome', 'estoque_atual', 'estoque_minimo')
            ->whereColumn('estoque_atual', '<', 'estoque_minimo')
            ->orderBy('nome', 'asc')
            ->get();
    }

    // Busca produtos de um tipo com estoque baixo e conta suas movimentações
    public static function getByTipoAndEstoqueBaixoComMovimentacoes($tipo)
{
    return DB::table('produtos')
        ->leftJoin('movimentacoes', 'produtos.id', '=', 'movimentacoes.produto_id')
        ->select('produtos.id', 'produtos.nome', 'produtos.estoque_atual', 
                'produtos.estoque_minimo', 
                DB::raw('COUNT(movimentacoes.id) as total_movimentacoes'))
        ->where('produtos.tipo', $tipo)
        ->whereColumn('produtos.estoque_atual', '<', 'produtos.estoque_minimo')
        ->groupBy('produtos.id', 'produtos.nome', 'produtos.estoque_atual', 'produtos.estoque_minimo')
        ->orderBy('produtos.nome', 'asc')
        ->get();
}
}

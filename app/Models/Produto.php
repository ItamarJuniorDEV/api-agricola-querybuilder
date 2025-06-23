<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Produto extends Model
{
    use HasFactory;
    
    protected $table = 'produtos';
    
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
    
    // Busca um produto específico pelo ID
    public static function getById($id)
    {
        return DB::table('produtos')
            ->select('*')
            ->where('id', $id)
            ->first();
    }
    
    // Busca as movimentações de um produto específico
    public static function getMovimentacoesByProdutoId($produtoId)
    {
        return DB::table('movimentacoes')
            ->select(
                'movimentacoes.id',
                'movimentacoes.tipo',
                'movimentacoes.quantidade',
                'movimentacoes.data_movimento',
                'movimentacoes.observacao',
                'movimentacoes.created_at',
                'produtos.nome as produto_nome'
            )
            ->join('produtos', 'produtos.id', '=', 'movimentacoes.produto_id')
            ->where('movimentacoes.produto_id', $produtoId)
            ->orderBy('movimentacoes.data_movimento', 'desc')
            ->orderBy('movimentacoes.created_at', 'desc')
            ->get();
    }
    
    // Cria um novo produto
    public static function create($dados)
    {
        return DB::table('produtos')->insertGetId([
            'nome' => $dados['nome'],
            'tipo' => $dados['tipo'],
            'unidade' => $dados['unidade'],
            'estoque_minimo' => $dados['estoque_minimo'],
            'estoque_atual' => $dados['estoque_atual'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
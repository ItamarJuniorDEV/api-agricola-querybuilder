<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProdutoController extends Controller
{
    // Declaração das variáveis privadas
    private $request;
    private $produtoModel;
    private $tipo;
    private $estoqueBaixo;
    private $produtoId;
    
    /**
     * Construtor - inicializa as variáveis
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->produtoModel = new Produto();
        
        // Inicializa os filtros do request
        $this->tipo = $this->request->get('tipo');
        $this->estoqueBaixo = $this->request->get('estoque_baixo');
        $this->produtoId = $this->request->route('id');
    }
    
    /**
     * Lista produtos com filtros opcionais
     * GET /api/produtos
     */
    public function listar(): JsonResponse
    {
        try {
            Log::info('[ProdutoController] Listando produtos', [
                'tipo' => $this->tipo,
                'estoque_baixo' => $this->estoqueBaixo
            ]);
            
            // Lógica de decisão baseada nos filtros
            if ($this->tipo && $this->estoqueBaixo === 'true') {
                $produtos = $this->produtoModel::getByTipoAndEstoqueBaixoComMovimentacoes($this->tipo);
                $filtrosAplicados = ['tipo' => $this->tipo, 'estoque_baixo' => true];
                
            } elseif ($this->tipo) {
                $produtos = $this->produtoModel::getByTipo($this->tipo);
                $filtrosAplicados = ['tipo' => $this->tipo];
                
            } elseif ($this->estoqueBaixo === 'true') {
                $produtos = $this->produtoModel::getEstoqueBaixo();
                $filtrosAplicados = ['estoque_baixo' => true];
                
            } else {
                $produtos = $this->produtoModel::getAll();
                $filtrosAplicados = [];
            }
            
            // Verifica se encontrou produtos
            if ($produtos->isEmpty()) {
                Log::warning('[ProdutoController] Nenhum produto encontrado', $filtrosAplicados);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Nenhum produto encontrado',
                    'filtros' => $filtrosAplicados,
                    'total' => 0,
                    'data' => []
                ]);
            }
            
            Log::info('[ProdutoController] Produtos encontrados', [
                'total' => $produtos->count()
            ]);
            
            return response()->json([
                'status' => 'success',
                'filtros' => $filtrosAplicados,
                'total' => $produtos->count(),
                'data' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ProdutoController] Erro ao listar produtos', [
                'erro' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar produtos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cria um novo produto
     * POST /api/produtos
     */
    public function store(): JsonResponse
    {
        try {
            // Validação dos dados
            $dadosProduto = $this->request->validate([
                'nome' => 'required|string|max:70',
                'tipo' => 'required|string|in:alimento,bebida,limpeza,higiene',
                'unidade' => 'required|string|in:un,kg,lt,cx',
                'estoque_minimo' => 'required|numeric|min:0',
                'estoque_atual' => 'required|numeric|min:0'
            ]);
            
            Log::info('[ProdutoController] Criando novo produto', $dadosProduto);
            
            // Cria o produto no banco
            $novoProdutoId = $this->produtoModel::create($dadosProduto);
            
            // Busca o produto criado para retornar
            $novoProduto = $this->produtoModel::getById($novoProdutoId);
            
            Log::info('[ProdutoController] Produto criado com sucesso', [
                'id' => $novoProdutoId
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Produto criado com sucesso',
                'data' => $novoProduto
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[ProdutoController] Erro de validação ao criar produto', [
                'erros' => $e->errors()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('[ProdutoController] Erro ao criar produto', [
                'erro' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao criar produto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mostra um produto específico
     * GET /api/produtos/{id}
     */
    public function show(): JsonResponse
    {
        try {
            // Validação do ID
            if (!$this->produtoId || !is_numeric($this->produtoId)) {
                Log::warning('[ProdutoController] ID do produto inválido', [
                    'id' => $this->produtoId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID do produto inválido'
                ], 400);
            }
            
            Log::info('[ProdutoController] Buscando produto', [
                'id' => $this->produtoId
            ]);
            
            // Busca o produto
            $produto = $this->produtoModel::getById($this->produtoId);
            
            // Verifica se encontrou o produto
            if (!$produto) {
                Log::warning('[ProdutoController] Produto não encontrado', [
                    'id' => $this->produtoId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ], 404);
            }
            
            Log::info('[ProdutoController] Produto encontrado', [
                'id' => $this->produtoId
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $produto
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ProdutoController] Erro ao buscar produto', [
                'id' => $this->produtoId,
                'erro' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar produto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mostra o histórico de movimentações de um produto
     * GET /api/produtos/{id}/historico
     */
    public function historico(): JsonResponse
    {
        try {
            // Validação do ID
            if (!$this->produtoId || !is_numeric($this->produtoId)) {
                Log::warning('[ProdutoController] ID do produto inválido para histórico', [
                    'id' => $this->produtoId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID do produto inválido'
                ], 400);
            }
            
            Log::info('[ProdutoController] Buscando histórico do produto', [
                'id' => $this->produtoId
            ]);
            
            // Verifica se o produto existe
            $produto = $this->produtoModel::getById($this->produtoId);
            
            if (!$produto) {
                Log::warning('[ProdutoController] Produto não encontrado para histórico', [
                    'id' => $this->produtoId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ], 404);
            }
            
            // Busca as movimentações
            $movimentacoes = $this->produtoModel::getMovimentacoesByProdutoId($this->produtoId);
            
            Log::info('[ProdutoController] Histórico encontrado', [
                'id' => $this->produtoId,
                'total_movimentacoes' => $movimentacoes->count()
            ]);
            
            return response()->json([
                'status' => 'success',
                'produto' => [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'estoque_atual' => $produto->estoque_atual
                ],
                'total_movimentacoes' => $movimentacoes->count(),
                'movimentacoes' => $movimentacoes
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ProdutoController] Erro ao buscar histórico', [
                'id' => $this->produtoId,
                'erro' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar histórico',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
<?php
// filepath: /Users/juliocosta/Documents/workspace/JCTech/challenge-api/app/Http/Controllers/ElasticsearchController.php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ElasticsearchController extends Controller
{
    /**
     * Reindexar todos os produtos no Elasticsearch
     */
    public function reindexAll(Request $request, ProductRepository $repository): JsonResponse
    {
        $totalIndexed = $repository->reindexAllProducts();
        
        return response()->json([
            'success' => true,
            'message' => "Reindexação concluída com sucesso",
            'total_indexed' => $totalIndexed
        ]);
    }
}
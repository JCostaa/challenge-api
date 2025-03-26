<?php
// filepath: /Users/juliocosta/Documents/workspace/JCTech/challenge-api/app/Console/Commands/CreateAlgoliaIndex.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CreateAlgoliaIndex extends Command
{
    protected $signature = 'algolia:create-index';
    protected $description = 'Create and configure Algolia index for products';

    public function handle()
    {
        $this->info('Iniciando configuração do índice Algolia...');
        
        // Verificar configurações do Algolia
        $appId = config('scout.algolia.id');
        $apiKey = config('scout.algolia.secret');
        
        if (empty($appId) || empty($apiKey)) {
            $this->error('As credenciais do Algolia não estão configuradas corretamente.');
            return Command::FAILURE;
        }
        
        // Usar Http para configurar o índice diretamente através da API REST do Algolia
        $indexName = (new Product())->searchableAs();
        $this->info("Configurando índice via API REST: {$indexName}");
        
        try {
            // Configurar o índice usando a API REST do Algolia
            $url = "https://{$appId}-1.algolianet.com/1/indexes/{$indexName}/settings";
            
            $response = Http::withHeaders([
                'X-Algolia-API-Key' => $apiKey,
                'X-Algolia-Application-Id' => $appId,
                'Content-Type' => 'application/json'
            ])->put($url, [
                'searchableAttributes' => [
                    'product_name',
                    'code',
                    'brands',
                    'categories',
                    'ingredients_text'
                ],
                'attributesForFaceting' => [
                    'searchable(brands)',
                    'searchable(categories)',
                    'searchable(main_category)',
                    'status'
                ],
                'customRanking' => [
                    'desc(updated_at)',
                    'desc(created_at)'
                ],
                'attributesToHighlight' => [
                    'product_name',
                    'ingredients_text'
                ],
                'hitsPerPage' => 20
            ]);
            
            if ($response->successful()) {
                $this->info('Índice configurado com sucesso via API REST!');
            } else {
                $this->error('Erro ao configurar índice: ' . $response->body());
                return Command::FAILURE;
            }
            
            // Importar dados para o Algolia
            if ($this->confirm('Deseja importar os produtos para o Algolia agora?', true)) {
                $this->call('scout:import', ['model' => Product::class]);
                $this->info('Produtos importados com sucesso!');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao configurar índice: ' . $e->getMessage());
            Log::error('Erro ao configurar índice Algolia: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
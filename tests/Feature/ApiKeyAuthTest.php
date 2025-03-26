<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyAuthTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_access_api_with_valid_key()
    {
        // Criar uma API key
        $apiKey = ApiKey::factory()->create();
        
        // Criar alguns produtos para testar a listagem
        Product::factory()->count(5)->create();
        
        // Fazer requisição com a API key
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->getJson('/api/products');
        
        // Verificar que a requisição foi bem-sucedida
        $response->assertStatus(200);
    }
    
    public function test_cannot_access_api_without_key()
    {
        // Fazer requisição sem API key
        $response = $this->getJson('/api/products');
        
        // Verificar que a requisição falhou com 401
        $response->assertStatus(401);
    }
    
    public function test_cannot_access_api_with_invalid_key()
    {
        // Fazer requisição com API key inválida
        $response = $this->withHeaders([
            'X-API-KEY' => 'invalid_key',
        ])->getJson('/api/products');
        
        // Verificar que a requisição falhou com 401
        $response->assertStatus(401);
    }
    
    public function test_cannot_access_restricted_endpoint_without_permission()
    {
        // Criar uma API key sem permissões específicas
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['products.read'] // apenas permissão de leitura
        ]);
        
        // Criar um produto para testar
        $product = Product::factory()->create();
        
        // Tentar excluir um produto (operação que exige products.write)
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->deleteJson("/api/products/{$product->code}");
        
        // Verificar que a requisição falhou com 403
        $response->assertStatus(403);
    }
    
    public function test_can_access_restricted_endpoint_with_correct_permission()
    {
        // Criar uma API key com permissão de escrita
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['products.write']
        ]);
        
        // Criar um produto para testar
        $product = Product::factory()->create();
        
        // Tentar excluir um produto
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->deleteJson("/api/products/{$product->code}");
        
        // Verificar que a requisição foi bem-sucedida
        $response->assertStatus(200);
    }
}

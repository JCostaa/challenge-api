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
      
        $apiKey = ApiKey::factory()->create();
        
        
        Product::factory()->count(5)->create();
        
        
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->getJson('/api/products');
        
       
        $response->assertStatus(200);
    }
    
    public function test_cannot_access_api_without_key()
    {
        
        $response = $this->getJson('/api/products');
        
     
        $response->assertStatus(401);
    }
    
    public function test_cannot_access_api_with_invalid_key()
    {
        
        $response = $this->withHeaders([
            'X-API-KEY' => 'invalid_key',
        ])->getJson('/api/products');
        
        
        $response->assertStatus(401);
    }
    
    public function test_cannot_access_restricted_endpoint_without_permission()
    {
      
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['products.read'] // apenas permissão de leitura
        ]);
        
        
        $product = Product::factory()->create();
        
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->deleteJson("/api/products/{$product->code}");
        

        $response->assertStatus(403);
    }
    
    public function test_can_access_restricted_endpoint_with_correct_permission()
    {
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['products.write']
        ]);
        
        $product = Product::factory()->create();
        
        $response = $this->withHeaders([
            'X-API-KEY' => $apiKey->key,
        ])->deleteJson("/api/products/{$product->code}");
        
        $response->assertStatus(200);
    }
}

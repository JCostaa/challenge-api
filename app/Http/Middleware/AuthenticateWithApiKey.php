<?php
// filepath: /Users/juliocosta/Documents/workspace/JCTech/challenge-api/app/Http/Middleware/AuthenticateWithApiKey.php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredPermission = null): Response
    {
        // Tenta obter a API key do cabeçalho
        $apiKey = $request->header('X-API-KEY');

        // Tentar obter do parâmetro de query caso não esteja presente no cabeçalho
        if (empty($apiKey)) {
            $apiKey = $request->query('api_key');
        }

        // Se ainda não temos uma API key, retorna erro
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'API key is required',
                'message' => 'Please provide a valid API key in the X-API-KEY header or api_key query parameter'
            ], 401);
        }

        // Busca a API key no banco
        $keyModel = ApiKey::where('key', $apiKey)->first();
        
        // Verifica se a API key existe
        if (!$keyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid'
            ], 401);
        }

        // Verifica se a API key é válida (não deletada e não expirada)
        if (!$keyModel->isValid()) {
            return response()->json([
                'error' => 'Expired API key',
                'message' => 'The provided API key has expired or been revoked'
            ], 401);
        }

        // Verifica se a API key pode ser usada no IP atual
        if (!$keyModel->canBeUsedFromIp()) {
            return response()->json([
                'error' => 'IP not allowed',
                'message' => 'Your IP address is not authorized to use this API key'
            ], 403);
        }

        // Verifica permissões específicas se necessário
        if ($requiredPermission && !$keyModel->hasPermission($requiredPermission)) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => 'This API key does not have permission to perform this action'
            ], 403);
        }

        // Registra o uso da API key
        $keyModel->markAsUsed();

        // Associar o usuário relacionado à API key, se houver
        if ($keyModel->user_id) {
            auth()->login($keyModel->user);
        }

        // Adiciona a API key ao request para possível uso futuro
        $request->apiKey = $keyModel;

        return $next($request);
    }
}
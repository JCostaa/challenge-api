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
        
        $apiKey = $request->header('X-API-KEY');

        if (empty($apiKey)) {
            $apiKey = $request->query('api_key');
        }

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'API key is required',
                'message' => 'Please provide a valid API key in the X-API-KEY header or api_key query parameter'
            ], 401);
        }

        $keyModel = ApiKey::where('key', $apiKey)->first();
        

        if (!$keyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid'
            ], 401);
        }

        if (!$keyModel->isValid()) {
            return response()->json([
                'error' => 'Expired API key',
                'message' => 'The provided API key has expired or been revoked'
            ], 401);
        }

        if (!$keyModel->canBeUsedFromIp()) {
            return response()->json([
                'error' => 'IP not allowed',
                'message' => 'Your IP address is not authorized to use this API key'
            ], 403);
        }

        if ($requiredPermission && !$keyModel->hasPermission($requiredPermission)) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => 'This API key does not have permission to perform this action'
            ], 403);
        }

        $keyModel->markAsUsed();

        if ($keyModel->user_id) {
            auth()->login($keyModel->user);
        }

        $request->apiKey = $keyModel;

        return $next($request);
    }
}
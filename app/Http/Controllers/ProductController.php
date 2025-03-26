<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetSearch;
use App\Http\Requests\UpdateProduct;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getAll(): JsonResponse
    {
        $response = $this->productService->getAll();

        return response()->json($response)
            ->setStatusCode($response['statusCode']);
    }

    public function getOneById(Product $product): JsonResponse
    {
        $response = $this->productService->getOneById($product);

        return response()->json($response)
            ->setStatusCode($response['statusCode']);
    }

    public function update(Product $product, UpdateProduct $request): JsonResponse
    {
        $response = $this->productService->update($product, $request->validated());

        return response()->json($response)
            ->setStatusCode($response['statusCode']);
    }

    public function getSearch(GetSearch $request): JsonResponse
    {
        $response = $this->productService->getSearch($request->validated());

        return response()->json($response)
            ->setStatusCode($response['statusCode']);
    }

    public function destroy(Product $product): JsonResponse
    {
        $response = $this->productService->destroy($product);

        return response()->json($response)
            ->setStatusCode($response['statusCode']);
    }
}

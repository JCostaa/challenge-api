<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Enum\ProductStatus;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function update(Product $product, $validatedData): array
    {
        DB::beginTransaction();

        try {
            $this->productRepository->update($product, $validatedData);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error(' FAILED TO UPDATE PRODUCT ', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'message' => 'Failed to update product',
                'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ];
        }

        DB::commit();

        return [
            'message' => 'Product updated',
            'data' => new ProductResource($product),
            'statusCode' => Response::HTTP_CREATED,
        ];
    }

    public function getAll(): array
    {
        $data = $this->productRepository->getAll();

        return [
            'data' => new ProductResource($data),
            'statusCode' => Response::HTTP_OK,
        ];
    }

    public function getOneById(Product $product): array
    {
        return [
            'data' => new ProductResource($product),
            'statusCode' => Response::HTTP_OK,
        ];
    }

    public function getSearch($request): array
    {
        $data = $this->productRepository->getSearch($request);

        return [
            'data' => new ProductResource($data),
            'statusCode' => Response::HTTP_OK,
        ];
    }

    public function destroy(Product $product): array
    {
        DB::beginTransaction();

        try {
            $this->productRepository->update($product, ['status' => ProductStatus::Trash]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error(' FAILED TO DELETE PRODUCT ', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'message' => 'Fail to delete product',
                'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ];
        }
        DB::commit();

        return [
            'message' => 'Product deleted',
            'data' => new ProductResource($product),
            'statusCode' => Response::HTTP_OK,
        ];
    }
}
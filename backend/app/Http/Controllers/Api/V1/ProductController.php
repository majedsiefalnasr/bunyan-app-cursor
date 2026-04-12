<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreateProductRequest;
use App\Http\Requests\Api\V1\StoreProductMediaRequest;
use App\Http\Requests\Api\V1\StoreProductVariantRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductMediaResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'per_page' => $request->get('per_page'),
        ];

        if ($request->filled('category')) {
            $filters['category'] = $request->string('category')->toString();
        }
        if ($request->filled('category_id')) {
            $filters['category_id'] = (int) $request->get('category_id');
        }
        if ($request->filled('supplier_id')) {
            $filters['supplier_id'] = (int) $request->get('supplier_id');
        }
        if ($request->filled('min_price')) {
            $filters['min_price'] = $request->get('min_price');
        }
        if ($request->filled('max_price')) {
            $filters['max_price'] = $request->get('max_price');
        }
        if ($request->filled('search')) {
            $filters['search'] = $request->string('search')->toString();
        }
        if ($request->boolean('in_stock')) {
            $filters['in_stock'] = true;
        }

        $products = $this->productService->paginateCatalog($filters);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'تم جلب المنتجات بنجاح',
            200
        );
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->loadDisplay($product);

        return $this->sendSuccess(
            new ProductResource($product),
            'تم جلب المنتج بنجاح',
            200
        );
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->create($request->validated());

        return $this->sendSuccess(
            new ProductResource($this->productService->loadDisplay($product)),
            'تم إنشاء المنتج بنجاح',
            201
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->productService->update($product, $request->validated());

        return $this->sendSuccess(
            new ProductResource($this->productService->loadDisplay($product)),
            'تم تحديث المنتج بنجاح',
            200
        );
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return $this->sendSuccess(null, 'تم حذف المنتج بنجاح', 200);
    }

    public function storeVariant(StoreProductVariantRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $variant = $this->productService->addVariant($product, $request->validated());

        return $this->sendSuccess(
            new ProductVariantResource($variant),
            'تم إنشاء المتغير بنجاح',
            201
        );
    }

    public function storeMedia(StoreProductMediaRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $media = $this->productService->addMedia($product, $request->validated());

        return $this->sendSuccess(
            new ProductMediaResource($media),
            'تمت إضافة الوسائط بنجاح',
            201
        );
    }
}

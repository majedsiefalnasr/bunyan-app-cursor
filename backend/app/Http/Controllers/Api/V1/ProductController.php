<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreateProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        $products = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'تم جلب المنتجات بنجاح',
            200
        );
    }

    public function show(Product $product): JsonResponse
    {
        return $this->sendSuccess(
            new ProductResource($product),
            'تم جلب المنتج بنجاح',
            200
        );
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'sku' => 'SKU-'.strtoupper(Str::random(10)),
            'category' => $request->category,
            'price' => $request->price,
            'quantity_in_stock' => $request->quantity,
        ]);

        return $this->sendSuccess(
            new ProductResource($product),
            'تم إنشاء المنتج بنجاح',
            201
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        if (array_key_exists('quantity', $data)) {
            $data['quantity_in_stock'] = $data['quantity'];
            unset($data['quantity']);
        }
        $product->update($data);

        return $this->sendSuccess(
            new ProductResource($product),
            'تم تحديث المنتج بنجاح',
            200
        );
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return $this->sendSuccess(null, 'تم حذف المنتج بنجاح', 200);
    }
}

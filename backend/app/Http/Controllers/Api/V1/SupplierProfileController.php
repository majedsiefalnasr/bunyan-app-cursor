<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SupplierVerificationStatus;
use App\Http\Requests\Api\V1\StoreSupplierProfileRequest;
use App\Http\Requests\Api\V1\UpdateSupplierProfileRequest;
use App\Http\Requests\Api\V1\VerifySupplierProfileRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\SupplierProfileResource;
use App\Models\SupplierProfile;
use App\Services\SupplierProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierProfileController extends BaseController
{
    public function __construct(private SupplierProfileService $service)
    {
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $filters = [
            'per_page' => $request->integer('per_page', 20),
            'verification_status' => $request->string('verification_status')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        $paginator = $this->service->listForAdmin($filters);

        return $this->sendSuccess(
            SupplierProfileResource::collection($paginator),
            'تم جلب ملفات الموردين بنجاح',
            200
        );
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'per_page' => $request->integer('per_page', 15),
            'city' => $request->string('city')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        $paginator = $this->service->listCatalog($filters);

        return $this->sendSuccess(
            SupplierProfileResource::collection($paginator),
            'تم جلب الموردين بنجاح',
            200
        );
    }

    public function show(Request $request, SupplierProfile $supplierProfile): JsonResponse
    {
        $profile = $this->service->getProfileForDisplay($request->user(), $supplierProfile->id);

        return $this->sendSuccess(
            new SupplierProfileResource($profile),
            'تم جلب ملف المورد بنجاح',
            200
        );
    }

    public function products(Request $request, SupplierProfile $supplierProfile): JsonResponse
    {
        $profile = $this->service->getProfileForDisplay($request->user(), $supplierProfile->id);
        $products = $this->service->listProductsForDisplay($request->user(), $profile);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'تم جلب منتجات المورد بنجاح',
            200
        );
    }

    public function store(StoreSupplierProfileRequest $request): JsonResponse
    {
        $profile = $this->service->createForContractor($request->user(), $request->validated());

        Log::info('Supplier profile created', [
            'action' => 'supplier_profile.created',
            'supplier_profile_id' => $profile->id,
            'user_id' => $request->user()->id,
        ]);

        return $this->sendSuccess(
            new SupplierProfileResource($profile->load('user:id,name,email,phone')),
            'تم إنشاء ملف المورد بنجاح',
            201
        );
    }

    public function update(UpdateSupplierProfileRequest $request, SupplierProfile $supplierProfile): JsonResponse
    {
        $profile = $this->service->updateProfile(
            $request->user(),
            $supplierProfile,
            $request->validated()
        );

        Log::info('Supplier profile updated', [
            'action' => 'supplier_profile.updated',
            'supplier_profile_id' => $profile->id,
            'user_id' => $request->user()->id,
        ]);

        return $this->sendSuccess(
            new SupplierProfileResource($profile->load('user:id,name,email,phone')),
            'تم تحديث ملف المورد بنجاح',
            200
        );
    }

    public function verify(VerifySupplierProfileRequest $request, SupplierProfile $supplierProfile): JsonResponse
    {
        $status = SupplierVerificationStatus::from($request->string('verification_status')->toString());
        $profile = $this->service->verify($request->user(), $supplierProfile, $status);

        Log::info('Supplier profile verification changed', [
            'action' => 'supplier_profile.verification_changed',
            'supplier_profile_id' => $profile->id,
            'user_id' => $request->user()->id,
            'verification_status' => $status->value,
        ]);

        return $this->sendSuccess(
            new SupplierProfileResource($profile),
            'تم تحديث حالة التحقق بنجاح',
            200
        );
    }
}

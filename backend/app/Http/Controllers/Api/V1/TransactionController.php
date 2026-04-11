<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::query();

        if ($request->user()->role !== UserRole::Admin) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            TransactionResource::collection($transactions),
            'تم جلب المعاملات بنجاح',
            200
        );
    }

    public function show(Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== auth()->id() && auth()->user()?->role !== UserRole::Admin) {
            return $this->sendError('غير مصرح', [], 403);
        }

        return $this->sendSuccess(
            new TransactionResource($transaction),
            'تم جلب المعاملة بنجاح',
            200
        );
    }
}

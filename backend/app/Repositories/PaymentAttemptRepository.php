<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Database\Eloquent\Model;

class PaymentAttemptRepository extends BaseRepository
{
    protected function model(): string
    {
        return PaymentAttempt::class;
    }

    public function record(Payment $payment, array $data): Model
    {
        return $this->newQuery()->create(array_merge($data, [
            'payment_id' => $payment->id,
        ]));
    }
}

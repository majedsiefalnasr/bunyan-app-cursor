<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\CreateTransactionRequest;
use Tests\TestCase;

class CreateTransactionRequestTest extends TestCase
{
    public function test_authorize_allows_request(): void
    {
        $request = new CreateTransactionRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_rules_are_defined(): void
    {
        $request = new CreateTransactionRequest;

        $this->assertSame([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:payment,withdrawal'],
            'reference' => ['nullable', 'string', 'max:255'],
        ], $request->rules());
    }

    public function test_messages_include_arabic_validation_text(): void
    {
        $request = new CreateTransactionRequest;

        $messages = $request->messages();

        $this->assertSame('المبلغ مطلوب', $messages['amount.required']);
        $this->assertSame('المبلغ يجب أن يكون رقم', $messages['amount.numeric']);
        $this->assertSame('نوع المعاملة مطلوب', $messages['type.required']);
        $this->assertSame('نوع المعاملة غير صحيح', $messages['type.in']);
    }
}

<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\UpdateProfileRequest;
use Tests\TestCase;

class UpdateProfileRequestTest extends TestCase
{
    public function test_authorize_allows_request(): void
    {
        $request = new UpdateProfileRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_rules_are_defined(): void
    {
        $request = new UpdateProfileRequest;

        $this->assertSame([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
        ], $request->rules());
    }

    public function test_messages_include_arabic_validation_text(): void
    {
        $request = new UpdateProfileRequest;

        $messages = $request->messages();

        $this->assertSame('الاسم يجب أن يكون نص', $messages['name.string']);
        $this->assertSame('رقم الهاتف يجب أن يكون نص', $messages['phone.string']);
    }
}

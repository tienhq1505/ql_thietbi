<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationLocaleTest extends TestCase
{
    public function test_validation_messages_are_translated_to_vietnamese(): void
    {
        $this->assertEquals('vi', config('app.locale'));
        $this->assertEquals('vi', app()->getLocale());

        $validator = Validator::make([], [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();

        $this->assertEquals('Trường Tên thiết bị không được để trống.', $errors->first('name'));
        $this->assertEquals('Trường địa chỉ email không được để trống.', $errors->first('email'));
    }
}

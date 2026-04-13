<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Any user can attempt to check status
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
            'subscription_key' => 'required|string',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'اسم المستخدم مطلوب',
            'password.required' => 'كلمة السر مطلوبة',
            'subscription_key.required' => 'مفتاح الاشتراك (الجهاز) مطلوب',
        ];
    }
}

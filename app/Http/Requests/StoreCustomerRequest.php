<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => 'اسم العميل',
            'email'      => 'البريد الإلكتروني',
            'phone'      => 'رقم الجوال',
            'tax_number' => 'الرقم الضريبي',
            'address'    => 'العنوان',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'email'    => 'صيغة :attribute غير صحيحة.',
            'max'      => 'الحد الأقصى لـ :attribute هو :max حرف.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountValueRules = ['required', 'numeric', 'min:0'];

        if ($this->input('discount_type') === 'percent') {
            $discountValueRules[] = 'max:100';
        }

        return [
            'type'                  => ['required', 'in:invoice,quotation'],
            'customer_id'           => ['required', 'integer', 'exists:customers,id'],
            'issue_date'            => ['required', 'date'],
            'due_date'              => ['nullable', 'date', 'after_or_equal:issue_date'],
            'valid_until'           => ['nullable', 'date', 'after_or_equal:issue_date'],
            'discount_type'         => ['required', 'in:percent,fixed'],
            'discount_value'        => $discountValueRules,
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'      => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type'                => 'نوع المستند',
            'customer_id'         => 'العميل',
            'issue_date'          => 'تاريخ الإصدار',
            'due_date'            => 'تاريخ الاستحقاق',
            'valid_until'         => 'صالح حتى',
            'discount_type'       => 'نوع الخصم',
            'discount_value'      => 'قيمة الخصم',
            'notes'               => 'ملاحظات',
            'items'               => 'البنود',
            'items.*.description' => 'وصف البند',
            'items.*.quantity'    => 'الكمية',
            'items.*.unit_price'  => 'سعر الوحدة',
            'items.*.tax_rate'    => 'نسبة الضريبة',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => 'حقل :attribute مطلوب.',
            'in'              => 'قيمة :attribute غير صحيحة.',
            'integer'         => 'حقل :attribute يجب أن يكون رقمًا صحيحًا.',
            'numeric'         => 'حقل :attribute يجب أن يكون رقمًا.',
            'date'            => 'حقل :attribute يجب أن يكون تاريخًا صحيحًا.',
            'after_or_equal'  => 'حقل :attribute يجب ألا يسبق تاريخ الإصدار.',
            'exists'          => 'العميل المحدد غير موجود.',
            'min'             => 'الحد الأدنى لـ :attribute هو :min.',
            'max'             => 'الحد الأقصى لـ :attribute هو :max.',
            'array'           => 'حقل :attribute غير صحيح.',
            'items.min'       => 'يجب إضافة بند واحد على الأقل.',
            'items.required'  => 'يجب إضافة بند واحد على الأقل.',
        ];
    }
}

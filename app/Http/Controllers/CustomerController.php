<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'customer' => [
                    'id'   => $customer->id,
                    'name' => $customer->name,
                ],
            ], 201);
        }

        return redirect()
            ->route('invoices.create')
            ->with('status', 'تمت إضافة العميل بنجاح.');
    }
}

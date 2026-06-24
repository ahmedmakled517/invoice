<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'شركة الأفق للتقنية', 'email' => 'info@ufuq.sa', 'phone' => '+966500000001', 'tax_number' => '300000000000003', 'address' => 'الرياض، حي العليا'],
            ['name' => 'مؤسسة النخبة التجارية', 'email' => 'sales@nukhba.sa', 'phone' => '+966500000002', 'tax_number' => '310000000000003', 'address' => 'جدة، حي الروضة'],
            ['name' => 'شركة البناء الحديث', 'email' => 'contact@binaa.sa', 'phone' => '+966500000003', 'tax_number' => '320000000000003', 'address' => 'الدمام، حي الشاطئ'],
            ['name' => 'متجر الإبداع للتجارة', 'email' => 'hello@ibdaa.sa', 'phone' => '+966500000004', 'tax_number' => '330000000000003', 'address' => 'مكة المكرمة'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}

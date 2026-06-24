<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_customer_via_json(): void
    {
        $response = $this->postJson(route('customers.store'), [
            'name'       => 'عميل جديد',
            'email'      => 'new@example.com',
            'phone'      => '+966500000000',
            'tax_number' => '300000000000003',
            'address'    => 'الرياض',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('customer.name', 'عميل جديد');
        $response->assertJsonStructure(['customer' => ['id', 'name']]);
        $this->assertDatabaseHas('customers', ['name' => 'عميل جديد', 'email' => 'new@example.com']);
    }

    public function test_it_creates_customer_via_form_and_redirects(): void
    {
        $response = $this->post(route('customers.store'), ['name' => 'عميل من الفورم']);

        $response->assertRedirect(route('invoices.create'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('customers', ['name' => 'عميل من الفورم']);
    }

    public function test_it_requires_customer_name(): void
    {
        $response = $this->postJson(route('customers.store'), ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_it_validates_email_format(): void
    {
        $response = $this->postJson(route('customers.store'), [
            'name'  => 'عميل',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }
}

@extends('layouts.app')

@section('title', 'إنشاء مستند جديد')

@section('content')
<div x-data="invoiceForm({
        defaultTaxRate: {{ (float) $defaultTaxRate }},
        currency: @js($currencyLabel),
        csrf: @js(csrf_token()),
        storeCustomerUrl: @js(route('customers.store')),
        customers: @js($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values()),
        old: @js([
            'type' => old('type'),
            'customer_id' => old('customer_id'),
            'discount_type' => old('discount_type'),
            'discount_value' => old('discount_value'),
            'items' => old('items'),
        ]),
     })"
     x-init="init()">

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>برجاء تصحيح الأخطاء التالية:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">نوع المستند</label>
                        <select name="type" class="form-select" x-model="type">
                            <option value="invoice">فاتورة</option>
                            <option value="quotation">عرض سعر</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">العميل</label>
                        <div class="input-group">
                            <select name="customer_id" class="form-select" x-model="customerId">
                                <option value="">— اختر العميل —</option>
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                            <button type="button" class="btn btn-brand" x-on:click="openCustomerModal()">+ عميل جديد</button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">تاريخ الإصدار</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', now()->toDateString()) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label" x-text="type === 'quotation' ? 'صالح حتى' : 'تاريخ الاستحقاق'"></label>
                        <input type="date" name="due_date" class="form-control"
                               value="{{ old('due_date') }}" x-show="type === 'invoice'" :disabled="type !== 'invoice'">
                        <input type="date" name="valid_until" class="form-control"
                               value="{{ old('valid_until') }}" x-show="type === 'quotation'" :disabled="type !== 'quotation'" x-cloak>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="card-title mb-0">بنود المستند</h2>
                    <button type="button" class="btn btn-brand btn-sm" x-on:click="addItem()">+ إضافة بند</button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>الوصف</th>
                                <th style="width:110px">الكمية</th>
                                <th style="width:140px">سعر الوحدة</th>
                                <th style="width:120px">الضريبة %</th>
                                <th style="width:150px">الإجمالي</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="item.id">
                                <tr>
                                    <td x-text="index + 1"></td>
                                    <td>
                                        <input type="text" class="form-control" :name="`items[${index}][description]`" x-model="item.description" placeholder="وصف الصنف أو الخدمة">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" class="form-control text-center" :name="`items[${index}][quantity]`" x-model.number="item.quantity">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control text-center" :name="`items[${index}][unit_price]`" x-model.number="item.unitPrice">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control text-center" :name="`items[${index}][tax_rate]`" x-model.number="item.taxRate">
                                    </td>
                                    <td class="text-nowrap fw-semibold" x-text="formatMoney(lineSubtotal(item))"></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" x-on:click="removeItem(index)" x-show="items.length > 1">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title mb-3">الخصم والملاحظات</h2>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">نوع الخصم</label>
                                <select name="discount_type" class="form-select" x-model="discountType">
                                    <option value="percent">نسبة %</option>
                                    <option value="fixed">مبلغ ثابت</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">قيمة الخصم</label>
                                <input type="number" step="0.01" min="0" name="discount_value" class="form-control" x-model.number="discountValue">
                            </div>
                            <div class="col-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="ملاحظات إضافية (اختياري)">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="card-title mb-3">الإجماليات</h2>
                        <div class="totals-row">
                            <span>الإجمالي قبل الخصم</span>
                            <span x-text="formatMoney(itemsSubtotal)"></span>
                        </div>
                        <div class="totals-row text-danger">
                            <span>الخصم</span>
                            <span x-text="'- ' + formatMoney(discountAmount)"></span>
                        </div>
                        <div class="totals-row">
                            <span>الصافي بعد الخصم</span>
                            <span x-text="formatMoney(netAfterDiscount)"></span>
                        </div>
                        <div class="totals-row">
                            <span>الضريبة</span>
                            <span x-text="formatMoney(taxTotal)"></span>
                        </div>
                        <div class="totals-row totals-grand">
                            <span>الإجمالي النهائي</span>
                            <span x-text="formatMoney(grandTotal)"></span>
                        </div>

                        <button type="submit" class="btn btn-brand w-100 mt-3 py-2">حفظ المستند</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template x-if="showCustomerModal">
        <div>
            <div class="modal-backdrop-custom" x-on:click="showCustomerModal = false"></div>
            <div class="modal-panel">
                <div class="card" style="max-width:520px;width:100%">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title mb-0">إضافة عميل جديد</h2>
                            <button type="button" class="btn-close" x-on:click="showCustomerModal = false"></button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" x-model="newCustomer.name">
                            <div class="text-danger small mt-1" x-text="customerErrors.name ? customerErrors.name[0] : ''"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" x-model="newCustomer.email">
                                <div class="text-danger small mt-1" x-text="customerErrors.email ? customerErrors.email[0] : ''"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">رقم الجوال</label>
                                <input type="text" class="form-control" x-model="newCustomer.phone">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" x-model="newCustomer.tax_number">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" x-model="newCustomer.address">
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-brand" x-on:click="saveCustomer()" :disabled="savingCustomer">
                                <span x-text="savingCustomer ? 'جارٍ الحفظ...' : 'حفظ العميل'"></span>
                            </button>
                            <button type="button" class="btn btn-light" x-on:click="showCustomerModal = false">إلغاء</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
    function invoiceForm(cfg) {
        const round2 = (n) => Math.round(((Number(n) || 0) + Number.EPSILON) * 100) / 100;

        return {
            cfg,
            type: 'invoice',
            customerId: '',
            discountType: 'percent',
            discountValue: 0,
            items: [],
            nextId: 1,
            showCustomerModal: false,
            savingCustomer: false,
            newCustomer: { name: '', email: '', phone: '', tax_number: '', address: '' },
            customerErrors: {},

            init() {
                const old = cfg.old || {};

                if (old.items && old.items.length) {
                    this.items = old.items.map((it) => ({
                        id: this.nextId++,
                        description: it.description ?? '',
                        quantity: Number(it.quantity) || 1,
                        unitPrice: Number(it.unit_price) || 0,
                        taxRate: it.tax_rate !== undefined && it.tax_rate !== null ? Number(it.tax_rate) : cfg.defaultTaxRate,
                    }));
                } else {
                    this.items = [this.blankItem(), this.blankItem(), this.blankItem()];
                }

                this.type = old.type || 'invoice';
                this.customerId = old.customer_id || '';
                this.discountType = old.discount_type || 'percent';
                this.discountValue = old.discount_value !== undefined && old.discount_value !== null ? Number(old.discount_value) : 0;
            },

            blankItem() {
                return { id: this.nextId++, description: '', quantity: 1, unitPrice: 0, taxRate: cfg.defaultTaxRate };
            },

            addItem() {
                this.items.push(this.blankItem());
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            lineSubtotal(item) {
                return round2((Number(item.quantity) || 0) * (Number(item.unitPrice) || 0));
            },

            get itemsSubtotal() {
                return round2(this.items.reduce((sum, it) => sum + this.lineSubtotal(it), 0));
            },

            get discountAmount() {
                const subtotal = this.itemsSubtotal;
                const value = Number(this.discountValue) || 0;
                if (value <= 0 || subtotal <= 0) return 0;
                if (this.discountType === 'fixed') return round2(Math.min(value, subtotal));
                return round2((subtotal * Math.min(value, 100)) / 100);
            },

            get discountRatio() {
                const subtotal = this.itemsSubtotal;
                return subtotal > 0 ? this.discountAmount / subtotal : 0;
            },

            lineTax(item) {
                const taxable = round2(this.lineSubtotal(item) * (1 - this.discountRatio));
                return round2((taxable * (Number(item.taxRate) || 0)) / 100);
            },

            get taxTotal() {
                return round2(this.items.reduce((sum, it) => sum + this.lineTax(it), 0));
            },

            get netAfterDiscount() {
                return round2(this.itemsSubtotal - this.discountAmount);
            },

            get grandTotal() {
                return round2(this.itemsSubtotal - this.discountAmount + this.taxTotal);
            },

            formatMoney(n) {
                return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + cfg.currency;
            },

            openCustomerModal() {
                this.customerErrors = {};
                this.newCustomer = { name: '', email: '', phone: '', tax_number: '', address: '' };
                this.showCustomerModal = true;
            },

            async saveCustomer() {
                this.savingCustomer = true;
                this.customerErrors = {};
                try {
                    const res = await fetch(cfg.storeCustomerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': cfg.csrf,
                        },
                        body: JSON.stringify(this.newCustomer),
                    });

                    if (res.status === 422) {
                        const data = await res.json();
                        this.customerErrors = data.errors || {};
                        return;
                    }

                    if (!res.ok) throw new Error('request failed');

                    const data = await res.json();
                    this.customers.push(data.customer);
                    this.customerId = data.customer.id;
                    this.showCustomerModal = false;
                } catch (e) {
                    alert('حدث خطأ أثناء حفظ العميل، حاول مرة أخرى.');
                } finally {
                    this.savingCustomer = false;
                }
            },
        };
    }
</script>
@endpush

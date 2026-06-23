<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        :root { --brand: #2563eb; }
        body { font-family: 'Cairo', system-ui, sans-serif; background: #f4f6fb; color: #1f2937; }
        .navbar-brand { font-weight: 700; }
        .card { border: none; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.04); }
        .card-title { font-weight: 700; font-size: 1rem; }
        .table > :not(caption) > * > * { padding: .6rem .5rem; }
        .totals-row { display: flex; justify-content: space-between; padding: .35rem 0; }
        .totals-grand { font-size: 1.25rem; font-weight: 700; color: var(--brand); border-top: 2px solid #e5e7eb; padding-top: .6rem; margin-top: .4rem; }
        .form-label { font-weight: 600; font-size: .9rem; }
        .btn-brand { background: var(--brand); color: #fff; }
        .btn-brand:hover { background: #1d4ed8; color: #fff; }
        .modal-backdrop-custom { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1050; }
        .modal-panel { position: fixed; inset: 0; z-index: 1060; display: flex; align-items: flex-start; justify-content: center; padding: 4rem 1rem; overflow-y: auto; }
        [x-cloak] { display: none !important; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
        <div class="container">
            <a class="navbar-brand text-primary" href="{{ route('invoices.create') }}">{{ config('app.name') }}</a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('invoices.create') }}">إنشاء مستند</a>
                <a class="nav-link" href="{{ route('invoices.index') }}">كل المستندات</a>
            </div>
        </div>
    </nav>

    <main class="container pb-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

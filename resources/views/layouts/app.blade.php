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

        .search-bar { display: flex; align-items: center; gap: .5rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: .4rem .9rem; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: border-color .15s, box-shadow .15s; }
        .search-bar:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .search-bar .search-icon { width: 20px; height: 20px; color: #9ca3af; flex: 0 0 auto; }
        .search-bar input { border: 0; outline: 0; background: transparent; flex: 1 1 auto; font-family: inherit; font-size: 1rem; min-width: 0; }
        .search-bar .search-clear { color: #9ca3af; font-size: 1.5rem; line-height: 1; text-decoration: none; padding: 0 .3rem; }
        .search-bar .search-clear:hover { color: #ef4444; }
        .search-bar .btn { border-radius: 9px; padding: .45rem 1.4rem; flex: 0 0 auto; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
        <div class="container">
            <a class="navbar-brand text-primary d-flex align-items-center gap-2" href="{{ route('invoices.create') }}">
                <img src="{{ asset('images/logo.png') }}" alt="logo" width="32" height="32">
                {{ config('app.name') }}
            </a>
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

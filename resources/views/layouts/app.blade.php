<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Pesanan Laundry')</title>
</head>
<body>
    <header>
        <h1>Sistem Pesanan Laundry</h1>
        <p>Pengelolaan pesanan berdasarkan jenis layanan.</p>
        <nav>
            <a href="{{ route('pesanan.index') }}">Daftar Pesanan</a>
        </nav>
        <hr>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>

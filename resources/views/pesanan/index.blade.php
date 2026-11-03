@extends('layouts.app')

@section('title', 'Daftar Pesanan Laundry')

@section('content')
    @if (session('success'))
        <p><strong>{{ session('success') }}</strong></p>
    @endif

    @if (session('error'))
        <p><strong>{{ session('error') }}</strong></p>
    @endif

    @if ($error)
        <p><strong>{{ $error }}</strong></p>
    @endif

    <section>
        <h2>Tambah Pesanan</h2>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('pesanan.store') }}" method="POST">
            @csrf

            <p>
                <label for="kode_pesanan">Kode Pesanan</label><br>
                <input type="text" id="kode_pesanan" name="kode_pesanan" value="{{ old('kode_pesanan') }}" maxlength="10" required>
            </p>

            <p>
                <label for="nama_pelanggan">Nama Pelanggan</label><br>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" maxlength="100" required>
            </p>

            <p>
                <label for="nomor_telepon">Nomor Telepon</label><br>
                <input type="text" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}" maxlength="20" required>
            </p>

            <p>
                <label for="berat_kg">Berat (kg)</label><br>
                <input type="number" id="berat_kg" name="berat_kg" value="{{ old('berat_kg') }}" min="1" max="20" step="1" required>
            </p>

            <p>
                <label for="jenis_layanan_id">Jenis Layanan</label><br>
                <select id="jenis_layanan_id" name="jenis_layanan_id" required>
                    <option value="">-- Pilih jenis layanan --</option>
                    @foreach ($jenisLayanan as $layanan)
                        <option value="{{ $layanan->id }}" @selected((string) old('jenis_layanan_id') === (string) $layanan->id)>
                            {{ $layanan->nama_layanan }}
                        </option>
                    @endforeach
                </select>
            </p>

            <button type="submit">Simpan Pesanan</button>
        </form>
    </section>

    <hr>

    <section>
        <h2>Cari dan Filter Pesanan</h2>

        <form action="{{ route('pesanan.index') }}" method="GET">
            <p>
                <label for="q">Kata Kunci</label><br>
                <input type="text" id="q" name="q" value="{{ $keyword }}" placeholder="Kode, nama pelanggan, atau nomor telepon">
            </p>

            <p>
                <label for="filter_layanan">Jenis Layanan</label><br>
                <select id="filter_layanan" name="jenis_layanan_id">
                    <option value="">Semua layanan</option>
                    @foreach ($jenisLayanan as $layanan)
                        <option value="{{ $layanan->id }}" @selected($jenisLayananId === (int) $layanan->id)>
                            {{ $layanan->nama_layanan }}
                        </option>
                    @endforeach
                </select>
            </p>

            <button type="submit">Terapkan</button>
            <a href="{{ route('pesanan.index') }}">Reset</a>
        </form>
    </section>

    <hr>

    <section>
        <h2>Daftar Pesanan</h2>

        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor Telepon</th>
                    <th>Berat (kg)</th>
                    <th>Status Berat</th>
                    <th>Jenis Layanan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanan as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->kode_pesanan }}</td>
                        <td>{{ $item->nama_pelanggan }}</td>
                        <td>{{ $item->nomor_telepon }}</td>
                        <td>{{ $item->berat_kg }}</td>
                        <td>
                            @if (statusBerat((int) $item->berat_kg) === 'Besar')
                                Besar
                            @else
                                Reguler
                            @endif
                        </td>
                        <td>{{ $item->nama_layanan }}</td>
                        <td>
                            <a href="{{ route('pesanan.edit', $item->id) }}">Edit</a>

                            <form action="{{ route('pesanan.destroy', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada pesanan yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

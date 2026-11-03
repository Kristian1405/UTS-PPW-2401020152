@extends('layouts.app')

@section('title', 'Edit Pesanan Laundry')

@section('content')
    <h2>Edit Pesanan {{ $pesanan->kode_pesanan }}</h2>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pesanan.update', $pesanan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_pesanan">Kode Pesanan</label><br>
            <input type="text" id="kode_pesanan" name="kode_pesanan" value="{{ old('kode_pesanan', $pesanan->kode_pesanan) }}" maxlength="10" required>
        </p>

        <p>
            <label for="nama_pelanggan">Nama Pelanggan</label><br>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan', $pesanan->nama_pelanggan) }}" maxlength="100" required>
        </p>

        <p>
            <label for="nomor_telepon">Nomor Telepon</label><br>
            <input type="text" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $pesanan->nomor_telepon) }}" maxlength="20" required>
        </p>

        <p>
            <label for="berat_kg">Berat (kg)</label><br>
            <input type="number" id="berat_kg" name="berat_kg" value="{{ old('berat_kg', $pesanan->berat_kg) }}" min="1" max="20" step="1" required>
        </p>

        <p>
            <label for="jenis_layanan_id">Jenis Layanan</label><br>
            <select id="jenis_layanan_id" name="jenis_layanan_id" required>
                <option value="">-- Pilih jenis layanan --</option>
                @foreach ($jenisLayanan as $layanan)
                    <option value="{{ $layanan->id }}" @selected((string) old('jenis_layanan_id', $pesanan->jenis_layanan_id) === (string) $layanan->id)>
                        {{ $layanan->nama_layanan }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Perbarui Pesanan</button>
        <a href="{{ route('pesanan.index') }}">Batal</a>
    </form>
@endsection

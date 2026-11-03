<?php

use Illuminate\Support\Facades\DB;

it('renders filtered laundry orders', function () {
    $serviceId = DB::table('jenis_layanan')->insertGetId([
        'nama_layanan' => 'Cuci Kering',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pesanan_laundry')->insert([
        'kode_pesanan' => 'LD00000001',
        'nama_pelanggan' => 'Ayu Lestari',
        'nomor_telepon' => '081200000001',
        'berat_kg' => 5,
        'jenis_layanan_id' => $serviceId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->get(route('pesanan.index', [
        'q' => 'Ayu',
        'jenis_layanan_id' => $serviceId,
    ]));

    $response
        ->assertOk()
        ->assertViewHas('keyword', 'Ayu')
        ->assertSeeText('LD00000001')
        ->assertSeeText('Ayu Lestari');
});

it('creates a laundry order with valid data', function () {
    $serviceId = DB::table('jenis_layanan')->insertGetId([
        'nama_layanan' => 'Cuci Setrika',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post(route('pesanan.store'), [
        'kode_pesanan' => 'LD00000002',
        'nama_pelanggan' => 'Rafi Akbar',
        'nomor_telepon' => '081200000002',
        'berat_kg' => 8,
        'jenis_layanan_id' => $serviceId,
    ]);

    $response
        ->assertRedirectToRoute('pesanan.index')
        ->assertSessionHas('success', 'Pesanan LD00000002 berhasil ditambahkan.');

    $this->assertDatabaseHas('pesanan_laundry', [
        'kode_pesanan' => 'LD00000002',
        'nama_pelanggan' => 'Rafi Akbar',
        'jenis_layanan_id' => $serviceId,
    ]);
});

it('updates an existing laundry order', function () {
    $serviceId = DB::table('jenis_layanan')->insertGetId([
        'nama_layanan' => 'Cuci Kering',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $orderId = DB::table('pesanan_laundry')->insertGetId([
        'kode_pesanan' => 'LD00000003',
        'nama_pelanggan' => 'Nina Putri',
        'nomor_telepon' => '081200000003',
        'berat_kg' => 3,
        'jenis_layanan_id' => $serviceId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->put(route('pesanan.update', $orderId), [
        'kode_pesanan' => 'LD00000003',
        'nama_pelanggan' => 'Nina Wijaya',
        'nomor_telepon' => '081200000003',
        'berat_kg' => 10,
        'jenis_layanan_id' => $serviceId,
    ]);

    $response
        ->assertRedirectToRoute('pesanan.index')
        ->assertSessionHas('success', 'Pesanan LD00000003 berhasil diperbarui.');

    $this->assertDatabaseHas('pesanan_laundry', [
        'id' => $orderId,
        'nama_pelanggan' => 'Nina Wijaya',
        'berat_kg' => 10,
    ]);
});

it('deletes an existing laundry order', function () {
    $serviceId = DB::table('jenis_layanan')->insertGetId([
        'nama_layanan' => 'Cuci Kering',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $orderId = DB::table('pesanan_laundry')->insertGetId([
        'kode_pesanan' => 'LD00000004',
        'nama_pelanggan' => 'Budi Santoso',
        'nomor_telepon' => '081200000004',
        'berat_kg' => 4,
        'jenis_layanan_id' => $serviceId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->delete(route('pesanan.destroy', $orderId));

    $response
        ->assertRedirectToRoute('pesanan.index')
        ->assertSessionHas('success', 'Pesanan berhasil dihapus.');

    $this->assertDatabaseMissing('pesanan_laundry', ['id' => $orderId]);
});

it('rejects an order with invalid required fields', function () {
    $response = $this->from(route('pesanan.index'))
        ->post(route('pesanan.store'), [
            'kode_pesanan' => '',
            'nama_pelanggan' => '',
            'nomor_telepon' => '',
            'berat_kg' => 21,
            'jenis_layanan_id' => '',
        ]);

    $response
        ->assertRedirectToRoute('pesanan.index')
        ->assertSessionHasErrors([
            'kode_pesanan' => 'Kode pesanan wajib diisi.',
            'nama_pelanggan' => 'Nama pelanggan wajib diisi.',
            'nomor_telepon' => 'Nomor telepon wajib diisi.',
            'berat_kg' => 'Berat cucian harus antara 1 sampai 20 kg.',
            'jenis_layanan_id' => 'Jenis layanan wajib dipilih.',
        ]);

    $this->assertDatabaseCount('pesanan_laundry', 0);
});

it('rejects duplicate order code and phone number', function () {
    $serviceId = DB::table('jenis_layanan')->insertGetId([
        'nama_layanan' => 'Cuci Kering',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('pesanan_laundry')->insert([
        'kode_pesanan' => 'LD00000005',
        'nama_pelanggan' => 'Sari Dewi',
        'nomor_telepon' => '081200000005',
        'berat_kg' => 5,
        'jenis_layanan_id' => $serviceId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post(route('pesanan.store'), [
        'kode_pesanan' => 'LD00000005',
        'nama_pelanggan' => 'Tono Wijaya',
        'nomor_telepon' => '081200000005',
        'berat_kg' => 6,
        'jenis_layanan_id' => $serviceId,
    ]);

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'kode_pesanan' => 'Kode pesanan sudah digunakan.',
            'nomor_telepon' => 'Nomor telepon sudah digunakan.',
        ]);

    $this->assertDatabaseCount('pesanan_laundry', 1);
});

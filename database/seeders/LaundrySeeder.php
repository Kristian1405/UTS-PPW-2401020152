<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaundrySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['Cuci Kering', 'Cuci Setrika'] as $namaLayanan) {
            DB::table('jenis_layanan')->updateOrInsert(
                ['nama_layanan' => $namaLayanan],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $layanan = DB::table('jenis_layanan')->pluck('id', 'nama_layanan');

        $pesanan = [
            [
                'kode_pesanan'    => 'LD00000001',
                'nama_pelanggan'  => 'Ayu Lestari',
                'nomor_telepon'   => '081200000001',
                'berat_kg'        => 5,
                'jenis_layanan_id' => $layanan['Cuci Kering'],
            ],
            [
                'kode_pesanan'    => 'LD00000002',
                'nama_pelanggan'  => 'Rafi Akbar',
                'nomor_telepon'   => '081200000002',
                'berat_kg'        => 8,
                'jenis_layanan_id' => $layanan['Cuci Setrika'],
            ],
            [
                'kode_pesanan'    => 'LD00000003',
                'nama_pelanggan'  => 'Nina Putri',
                'nomor_telepon'   => '081200000003',
                'berat_kg'        => 3,
                'jenis_layanan_id' => $layanan['Cuci Kering'],
            ],
        ];

        foreach ($pesanan as $data) {
            DB::table('pesanan_laundry')->updateOrInsert(
                ['kode_pesanan' => $data['kode_pesanan']],
                $data + ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

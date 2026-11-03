<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_laundry', function (Blueprint $table) {
            $table->id();
            $table->char('kode_pesanan', 10)->unique();
            $table->string('nama_pelanggan', 100);
            $table->string('nomor_telepon', 20)->unique();
            $table->unsignedSmallInteger('berat_kg')
                ->check('berat_kg BETWEEN 1 AND 20');
            $table->foreignId('jenis_layanan_id')
                ->constrained('jenis_layanan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_laundry');
    }
};

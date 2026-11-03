<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->unsignedSmallInteger('berat_kg');
            $table->foreignId('jenis_layanan_id')
                ->constrained('jenis_layanan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE pesanan_laundry ADD CONSTRAINT chk_berat_kg CHECK (berat_kg BETWEEN 1 AND 20)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_laundry');
    }
};

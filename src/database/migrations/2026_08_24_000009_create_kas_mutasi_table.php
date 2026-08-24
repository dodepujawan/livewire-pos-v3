<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_mutasi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cabang_id')
                ->constrained('cabang')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('tipe', ['MASUK', 'KELUAR']);

            $table->enum('sumber', [
                'PENJUALAN',
                'SETOR',
                'TARIK',
                'REFUND',
                'LAIN',
            ])->default('PENJUALAN');

            $table->foreignId('transaksi_id')
                ->nullable()
                ->constrained('transaksi')
                ->nullOnDelete();

            $table->decimal('jumlah', 15, 2);

            $table->decimal('saldo_akhir', 15, 2)->nullable();

            $table->string('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_mutasi');
    }
};

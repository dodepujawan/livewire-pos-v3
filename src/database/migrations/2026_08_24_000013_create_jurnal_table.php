<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->string('nomor_jurnal')->unique();
            $table->text('keterangan')->nullable();

            $table->foreignId('transaksi_id')
                ->nullable()
                ->constrained('transaksi')
                ->nullOnDelete();

            $table->foreignId('cabang_id')
                ->constrained('cabang')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};

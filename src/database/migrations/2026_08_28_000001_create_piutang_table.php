<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->string('customer');
            $table->string('nomor_piutang')->unique();
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('sisa', 15, 2)->default(0);
            $table->enum('status', ['BELUM_LUNAS', 'LUNAS'])->default('BELUM_LUNAS');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};

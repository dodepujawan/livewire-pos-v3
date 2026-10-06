<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelunasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->enum('jenis', ['PIUTANG', 'HUTANG']);
            $table->unsignedBigInteger('referensi_id');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->enum('metode_bayar', ['TUNAI', 'TRANSFER', 'QRIS']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelunasan');
    }
};

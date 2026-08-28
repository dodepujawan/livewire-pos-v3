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

        // FK ke piutang atau hutang (nullable, satu-satu)
        Schema::table('pelunasan', function (Blueprint $table) {
            $table->foreign('referensi_id')
                ->constrained('piutang')
                ->nullOnDelete();
            $table->foreign('referensi_id')
                ->constrained('hutang')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelunasan');
    }
};

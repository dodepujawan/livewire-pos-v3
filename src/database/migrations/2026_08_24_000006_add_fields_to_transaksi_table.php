<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->after('tanggal');
            $table->foreignId('user_id')->nullable()->after('cabang_id');
            $table->enum('status', ['SELESAI', 'BATAL', 'PIUTANG'])->default('SELESAI')->after('customer');
            $table->enum('metode_bayar', ['TUNAI', 'TRANSFER', 'QRIS'])->default('TUNAI')->after('status');
            $table->decimal('bayar', 15, 2)->default(0)->after('metode_bayar');
            $table->decimal('kembali', 15, 2)->default(0)->after('bayar');
            $table->decimal('diskon_total', 15, 2)->default(0)->after('kembali');
            $table->text('catatan')->nullable()->after('diskon_total');

            $table->foreign('cabang_id')->references('id')->on('cabang')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['cabang_id']);
            $table->dropColumn([
                'cabang_id',
                'user_id',
                'status',
                'metode_bayar',
                'bayar',
                'kembali',
                'diskon_total',
                'catatan',
            ]);
        });
    }
};

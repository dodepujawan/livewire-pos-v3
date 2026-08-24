<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_mutasi', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->after('barang_id');
            $table->foreignId('transaksi_id')->nullable()->after('cabang_id');
            $table->foreignId('barang_satuan_id')->nullable()->after('transaksi_id');
            $table->decimal('qty_satuan', 15, 2)->nullable()->after('qty');

            $table->foreign('cabang_id')->references('id')->on('cabang')->nullOnDelete();
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->nullOnDelete();
            $table->foreign('barang_satuan_id')->references('id')->on('barang_satuan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stok_mutasi', function (Blueprint $table) {
            $table->dropForeign(['barang_satuan_id']);
            $table->dropForeign(['transaksi_id']);
            $table->dropForeign(['cabang_id']);
            $table->dropColumn([
                'cabang_id',
                'transaksi_id',
                'barang_satuan_id',
                'qty_satuan',
            ]);
        });
    }
};

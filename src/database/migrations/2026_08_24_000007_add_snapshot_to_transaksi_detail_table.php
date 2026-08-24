<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->decimal('harga_beli', 15, 2)->default(0)->after('harga');
            $table->string('nama_barang')->nullable()->after('harga_beli');
            $table->string('nama_satuan')->nullable()->after('nama_barang');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->dropColumn([
                'nama_satuan',
                'nama_barang',
                'harga_beli',
            ]);
        });
    }
};

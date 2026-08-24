<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_satuan', function (Blueprint $table) {
            $table->decimal('harga_beli', 18, 2)->default(0)->after('harga_jual');
            $table->boolean('is_default')->default(false)->after('harga_beli');
        });
    }

    public function down(): void
    {
        Schema::table('barang_satuan', function (Blueprint $table) {
            $table->dropColumn(['harga_beli', 'is_default']);
        });
    }
};

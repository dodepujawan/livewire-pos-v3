<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pembelian_id')
                ->constrained('pembelian')
                ->cascadeOnDelete();

            $table->foreignId('barang_id')
                ->constrained('barang');

            $table->foreignId('barang_satuan_id')
                ->constrained('barang_satuan');

            $table->decimal('qty', 15, 2);

            $table->decimal('harga_beli', 15, 2);

            $table->decimal('subtotal', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail');
    }
};

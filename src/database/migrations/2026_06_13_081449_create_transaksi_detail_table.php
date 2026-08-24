<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaksi_id')
                ->constrained('transaksi')
                ->cascadeOnDelete();

            $table->foreignId('barang_id')
                ->constrained('barang');

            $table->foreignId('barang_satuan_id')
                ->constrained('barang_satuan');

            $table->decimal('qty',15,2);

            $table->decimal('harga',15,2);

            $table->decimal('subtotal',15,2);

            // qty dikonversi ke pcs
            $table->integer('qty_pcs');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};

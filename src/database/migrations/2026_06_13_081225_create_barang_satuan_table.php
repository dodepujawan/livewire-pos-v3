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
        Schema::create('barang_satuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                ->constrained('barang')
                ->cascadeOnDelete();
            $table->string('nama_satuan');
            $table->integer('konversi');
            $table->decimal(
                'harga_jual',
                18,
                2
            )->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_satuan');
    }
};

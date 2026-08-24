<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_beli')->unique();

            $table->foreignId('cabang_id')
                ->constrained('cabang')
                ->cascadeOnDelete();

            $table->string('supplier');

            $table->date('tanggal');

            $table->decimal('total', 15, 2)->default(0);

            $table->enum('status', ['ORDER', 'TERIMA', 'BATAL'])
                ->default('ORDER');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};

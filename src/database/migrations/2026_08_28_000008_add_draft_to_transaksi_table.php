<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM('DRAFT', 'SELESAI', 'BATAL', 'PIUTANG') DEFAULT 'SELESAI'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM('SELESAI', 'BATAL', 'PIUTANG') DEFAULT 'SELESAI'");
    }
};

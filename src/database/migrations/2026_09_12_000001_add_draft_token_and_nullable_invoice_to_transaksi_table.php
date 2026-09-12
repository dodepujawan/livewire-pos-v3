<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->uuid('draft_token')->nullable()->unique()->after('id');
        });

        \DB::statement('ALTER TABLE transaksi MODIFY COLUMN nomor_transaksi VARCHAR(255) NULL');

        \DB::table('transaksi')
            ->where('status', 'DRAFT')
            ->get(['id'])
            ->each(function ($draft): void {
                \DB::table('transaksi')
                    ->where('id', $draft->id)
                    ->update([
                        'draft_token' => (string) Str::uuid(),
                        'nomor_transaksi' => null,
                    ]);
            });
    }

    public function down(): void
    {
        if (\DB::table('transaksi')->whereNull('nomor_transaksi')->exists()) {
            throw new \RuntimeException('Cannot rollback while draft transactions without invoices exist.');
        }

        \DB::statement('ALTER TABLE transaksi MODIFY COLUMN nomor_transaksi VARCHAR(255) NOT NULL');

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropUnique('transaksi_draft_token_unique');
            $table->dropColumn('draft_token');
        });
    }
};

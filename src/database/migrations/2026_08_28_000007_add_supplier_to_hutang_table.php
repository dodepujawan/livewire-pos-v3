<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hutang', function (Blueprint $table) {
            if (!Schema::hasColumn('hutang', 'supplier')) {
                $table->string('supplier')->after('pembelian_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hutang', function (Blueprint $table) {
            if (Schema::hasColumn('hutang', 'supplier')) {
                $table->dropColumn('supplier');
            }
        });
    }
};

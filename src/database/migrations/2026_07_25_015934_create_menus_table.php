<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {

            $table->id();
            // Parent Menu
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menus')
                ->restrictOnDelete();
            // Route Menu
            $table->foreignId('system_route_id')
                ->nullable()
                ->unique()
                ->constrained('system_routes')
                ->restrictOnDelete();
            // Judul Menu
            $table->string('title');
            // Icon
            $table->string('icon')->nullable();
            // Urutan
            $table->unsignedInteger('sort_order')->default(0);
            // Sidebar
            $table->boolean('is_sidebar')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};

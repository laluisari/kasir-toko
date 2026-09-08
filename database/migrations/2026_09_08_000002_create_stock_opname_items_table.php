<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('stok_sistem')->default(0);
            $table->integer('stok_fisik')->nullable();
            $table->integer('selisih')->nullable();
            $table->integer('terjual')->default(0); // referensi: total terjual dari table sales
            $table->string('status')->default('belum'); // belum | pas | selisih
            $table->integer('stok_penetapan')->nullable(); // opsional untuk penetapan manual
            $table->string('catatan')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->unique(['stock_opname_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
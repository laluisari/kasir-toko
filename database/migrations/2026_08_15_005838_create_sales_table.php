<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            
            // Snapshot Produk saat transaksi
            $table->string('product_name');
            $table->unsignedBigInteger('cost_price')->default(0);       // Modal snapshot
            $table->unsignedBigInteger('selling_price');                // Harga jual snapshot
            
            // Diskon & Jumlah Pembelian
            $table->unsignedBigInteger('discount')->default(0);         // Diskon manual / per item
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('subtotal');                     // ((selling_price - discount) * quantity)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
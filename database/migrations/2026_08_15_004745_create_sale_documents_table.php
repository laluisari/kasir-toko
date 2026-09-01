<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_documents', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();                 // Nomor Nota/Struk
            $table->unsignedBigInteger('subtotal')->default(0);         // Total belanja sebelum diskon
            $table->unsignedBigInteger('discount_total')->default(0);   // Akumulasi total diskon
            $table->unsignedBigInteger('total_price');                  // Total yang harus dibayar
            $table->unsignedBigInteger('paid_amount')->default(0);      // Uang yang diserahkan pembeli
            $table->unsignedBigInteger('change_amount')->default(0);    // Uang kembalian
            $table->string('payment_method')->default('cash');          // cash, qris, transfer
            $table->string('status')->default('completed');             // completed, pending, canceled
            $table->string('customer_note')->nullable();                // Catatan antrean pending
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_documents');
    }
};
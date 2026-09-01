<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->unsignedBigInteger('cost_price')->default(0);       // Modal / Harga Beli
            $table->unsignedBigInteger('selling_price');                // Harga Jual Normal
            $table->unsignedBigInteger('discount')->default(0);         // Diskon master (potongan nominal Rp)
            $table->integer('stock')->default(0);
            $table->string('unit')->default('pcs');                     // pcs, bks, botol, kg
            $table->string('image', 255)->nullable();                   // Eksplisit dengan max length
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

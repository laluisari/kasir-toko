<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_documents', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index(['created_at', 'product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['stock']);
        });
    }

    public function down(): void
    {
        Schema::table('sale_documents', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stock']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_documents', function (Blueprint $table) {
            $table->string('payment_type')->default('full')->after('payment_method');
            $table->boolean('is_debt')->default(false)->after('payment_type');
            $table->foreignId('buyer_id')->nullable()->after('is_debt')->constrained('buyers')->nullOnDelete();
            $table->date('due_date')->nullable()->after('buyer_id');
            $table->unsignedBigInteger('down_payment')->default(0)->after('due_date');
            $table->unsignedBigInteger('debt_remaining')->default(0)->after('down_payment');
            $table->text('debt_note')->nullable()->after('debt_remaining');
        });
    }

    public function down(): void
    {
        Schema::table('sale_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('buyer_id');
            $table->dropColumn([
                'payment_type',
                'is_debt',
                'due_date',
                'down_payment',
                'debt_remaining',
                'debt_note',
            ]);
        });
    }
};

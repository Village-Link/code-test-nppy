<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->nullable()->after('amount');
            $table->string('payment_screenshot')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('payment_screenshot');
        });
    }

    public function down(): void
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'payment_screenshot', 'submitted_at']);
        });
    }
};

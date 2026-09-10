<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained();
            $table->unsignedTinyInteger('installment_number');
            $table->date('due_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique([
                'loan_application_id',
                'installment_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};

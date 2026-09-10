<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('assigned_reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('term_months');
            $table->decimal('interest_rate', 5, 2);
            $table->string('purpose');
            $table->text('supporting_notes')->nullable();
            $table->text('decision_notes')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->date('application_date');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
